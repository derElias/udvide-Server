<?php
require_once 'access_vfc.php';
require_once 'access_DB.php';
require_once 'helper.php';

echo (new crudFormHandler())->handleForm();
class crudFormHandler
{
    private $postData;
    /** @var  access_vfc */
    private $vwsRequest;
    /** @var  HTTP_Request2_Response */
    private $vwsResponse;
    private $callCounter = 0;
    private $echoMessage = '';

     public function handleForm()
    {
        // this stops users from aborting the script execution when the Vuforia Cloud and the Server are out of sync
        $prevIgnoreUserAbort = ignore_user_abort(true);
        $result = $this->DoNotTouchHandleForm();
        ignore_user_abort($prevIgnoreUserAbort);
        return $result === false ? $this->echoMessage : $result;
    }

    private function DoNotTouchHandleForm()
    {
        $this->postData = purifyUserData();
        if ($_SERVER["REQUEST_METHOD"] == "POST") { // if form submit

            // don't trust the client: purify data
            $this->postData = purifyUserData();
            $this->errorPrevention();
            $this->postData['udvideVerb'] = mb_strtolower($this->postData['udvideVerb']);

            $perm = getPermissions($this->postData['username'], $this->postData['passHash']);
            // is login invalid? -> Error
            if ($perm === false) {
                $this->echoMessage .= 'Invalid login (Bad password or username)';
                return false;
            }

            // is client or admin? (->editPerm)
            $editPerm = $perm[0] > 0; // true when Admin or Client

            // create if $editPerm
            if ($this->postData['udvideVerb'] == 'create' && $editPerm) {
                return $this->createTarget();
            }

            // delete if editPerm
            if ($this->postData['udvideVerb'] == 'delete' && $editPerm) {
                return $this->deleteTarget();
            }

            // also grant editPerm if Editor for specified marker
            if (!$editPerm) { // true when Editor has Permissions
                foreach ($perm[1] as $tid) {
                    $editPerm = $tid === $this->postData['t_id'];
                    if ($editPerm)
                        break;
                }
            }

            if ($this->postData['access'] == 'update' && $editPerm) {
                return $this->updateTarget();
            }
            $this->echoMessage .= 'Invalid command?';
            return false;
        } else {
            $this->echoMessage .= "This site is used to evaluate CRUD Form requests.\n
            Please use the form or consult the Documentation for more information";
            return false;
        }
    }

    /**
     * @return bool
     */
    private function createTarget(): bool
    {
        do {
            $retry = false;
            try {
                $this->vwsRequest = (new access_vfc())
                    ->setTargetName($this->postData['t_name'])
                    ->setImage($this->postData['t_image'])
                    ->setMeta('/error.php?error=targetNotLinkedToDbYet')// redirect marker to Inform the marker still needs a moment (max 15 min according to API Doc)
                    ->setActiveflag(false)// Target Inactive until DB synced
                    ->setAccessMethod('create');
                $this->vwsResponse = $this->vwsRequest->execute();
            } catch (VuforiaAccessAPIException $e) { // Errors we can potentially fix before the request is actually sent out
                if ($this->handleVAAPIE($e)) {
                    $this->vwsResponse = $this->vwsRequest->execute();
                } else {
                    return false;
                }
            } catch (HttpRequestException $e) {
                $retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    $this->echoMessage .= var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                    return false;
                }
            }
        } while ($retry);

        $this->callCounter = 0;
        while ($this->errorHandleVFResponse() === true) {
            $this->vwsResponse = $this->vwsRequest->execute();
        }
        if ($this->errorHandleVFResponse() === false) {
            $this->echoMessage .= "Please review your input!";
            return false;
        }

        $vwsResponseBody = json_decode($this->vwsResponse->getBody());
        $t_id = $vwsResponseBody['target_id'];
        $tr_id = $vwsResponseBody['transaction_id'];

        $this->logTransaction($tr_id,$t_id);

        // Sync DBs and insert our own stuff
        $sql = "INSERT INTO udvide.Targets (t_id,t_owner,xpos,ypos,map) VALUES ($t_id,?,?,?,?);";
        access_DB::prepareExecuteGetStatement($sql, [$this->postData['username'], $this->postData['xPos'], $this->postData['yPos'], $this->postData['map']]);

        // Update VF DB to the now named target page
        do {
            $retry = false;
            try {
                $this->vwsRequest = (new access_vfc())
                    ->setTargetId($t_id)
                    ->setMeta("/clientRequest.php?t=$t_id")
                    ->setActiveflag($this->postData['activeFlag'])
                    ->setAccessMethod('update');
                $this->vwsResponse = $this->vwsRequest->execute();
            } catch (VuforiaAccessAPIException $e) { // Errors we can potentially fix before the request is actually sent out
                if ($this->handleVAAPIE($e)) {
                    $this->vwsResponse = $this->vwsRequest->execute();
                } else {
                    return false;
                }
            } catch (HttpRequestException $e) {
                $retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    $this->echoMessage .= var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                    return false;
                }
            }
        } while($retry);

        $this->callCounter = 0;
        while ($this->errorHandleVFResponse()===true) {
            $this->vwsResponse = $this->vwsRequest->execute();
        }
        if ($this->errorHandleVFResponse()===false) {
            $this->echoMessage .= "Please review your input!";
            return false;
        }
        $vwsResponseBody = json_decode($this->vwsResponse->getBody());
        $t_id = $vwsResponseBody['target_id'];
        $tr_id = $vwsResponseBody['transaction_id'];
        $this->logTransaction($tr_id,$t_id);
        return true;
    }

    /**
     * @return bool
     */
    private function updateTarget(): bool
    {
        do {
            $updateVWS = false;
            $retry = false;
            try {
                $vwsa = new access_vfc();
                $vwsa->setTargetId($this->postData['t_id'])
                    ->setAccessMethod('update');

                if (isset($this->postData['t_name'])) {
                    $vwsa->setTargetName($this->postData['t_name']);
                    $updateVWS = true;
                }
                if (isset($this->postData['t_image'])) {
                    $vwsa->setImage($this->postData['t_image']);
                    $updateVWS = true;
                }
                if (isset($this->postData['activeFlag'])) {
                    $vwsa->setActiveflag($this->postData['activeFlag']);
                    $updateVWS = true;
                }

                if ($updateVWS) {
                    $this->vwsResponse = $vwsa->execute();
                }
            } catch (VuforiaAccessAPIException $e) {
                if ($this->handleVAAPIE($e)) {
                    $this->vwsResponse = $this->vwsRequest->execute();
                } else {
                    return false;
                }
            } catch (HttpRequestException $e) {
                $retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    $this->echoMessage .= var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                    return false;
                }
            }
        } while ($retry);

        $this->callCounter = 0;
        while ($this->errorHandleVFResponse() === true) {
            $this->vwsResponse = $this->vwsRequest->execute();
        }
        if ($this->errorHandleVFResponse() === false) {
            $this->echoMessage .= "Please review your input!";
            return false;
        }

        if ($updateVWS) {
            $vwsResponseBody = json_decode($this->vwsResponse->getBody());
            $t_id = $vwsResponseBody['target_id'];
            $tr_id = $vwsResponseBody['transaction_id'];

            $this->logTransaction($tr_id,$t_id);
        }

        if ((!empty($this->postData['xPos']))
            || (!empty($this->postData['yPos']))
            || (!empty($this->postData['map']))) {

            $sql = /** @lang text */
                "UPDATE udvide.Targets SET ";

            $ins = [];
            if (!empty($this->postData['xPos'])) {
                $sql .= "xpos = '?'";
                $ins[0] = $this->postData['xPos'];

                if (!empty($this->postData['yPos'])) {
                    $sql .= ", ypos = '?'";
                    $ins[1] = $this->postData['yPos'];

                    if (!empty($this->postData['map'])) {
                        $sql .= ", map = '?'";
                        $ins[2] = $this->postData['map'];
                    }
                } else {
                    $sql .= ", map = '?'";
                    $ins[1] = $this->postData['map'];
                }
            } elseif (!empty($this->postData['yPos'])) {
                $sql .= "ypos = '?'";
                $ins[0] = $this->postData['yPos'];

                if (!empty($this->postData['map'])) {
                    $sql .= ", map = '?'";
                    $ins[1] = $this->postData['map'];
                }
            } else {
                $sql .= "map = '?'";
                $ins[0] = $this->postData['map'];
            }

            $sql .= " WHERE t_id = ?;";
            $ins[] = $this->postData['t_id'];
            access_DB::prepareExecuteGetStatement($sql, $ins);
        }
        return true;
    }

    /**
     * @return bool
     */
    private function deleteTarget(): bool
    {
        do {
            $retry = false;
            try {
                $vwsa = new access_vfc();
                $vwsa->setTargetId($this->postData['t_id'])
                    ->setAccessMethod('delete');
            } catch (VuforiaAccessAPIException $e) {
                if ($this->handleVAAPIE($e)) {
                    $this->vwsResponse = $this->vwsRequest->execute();
                } else {
                    return false;
                }
            } catch (HttpRequestException $e) {
                $retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    $this->echoMessage .= var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                    return false;
                }
            }
        } while ($retry);

        $this->callCounter = 0;
        while ($this->errorHandleVFResponse() === true) {
            $this->vwsResponse = $this->vwsRequest->execute();
        }
        if ($this->errorHandleVFResponse() === false) {
            $this->echoMessage .= "Please review your input!";
            return false;
        }

        $sql = 'DELETE FROM udvide.Targets WHERE t_id = ?';
        access_DB::prepareExecuteGetStatement($sql,$this->postData['t_id']);

        return true;
    }

    /**
     * @return bool|null
     */
    private function errorHandleVFResponse(): bool
    {
        $this->callCounter++;
        $return = false;

        switch ($this->vwsResponse->getStatus()) {
            case 200: // ok
            case 201: // TargetCreated
                return null; // no error
            case 403:
                switch (json_decode($this->vwsResponse->getBody())->status) {
                    case 'TargetNameExists':
                        $this->postData['t_name'] = $this->postData['t_name'].' 2';
                        $this->vwsRequest->setTargetName($this->postData['t_name']);
                        $return = false;
                        break;
                }
                break;
            case 404: // UnknownTarget
                $this->echoMessage .= 'Image has been deleted from other source.';
                $return = false;
                break;
            case 422:
                switch (json_decode($this->vwsResponse->getBody())->status) {
                    case 'BadImage':
                        $this->echoMessage .= 'Image seems bad. please submit only in a <a href="wiki.php?a=supportedImageFormats">supported format</a>'; //ToDo
                        $return = false;
                        break;
                    case 'ImageTooLarge': // ToDo this should be preventive
                        $this->echoMessage .= 'Image seems too large and we couldn\'t fix it automatically.';
                        $return = false;
                        break;
                    case 'MetadataTooLarge':
                        $this->echoMessage .= 'Metadata seems too large. This should not happen.';
                        $return = false;
                        break;
                }
                break;
            case 500: // VuFo Server internal
                $return = true;
                break;
            default:
                $this->echoMessage .= 'Vuforia Encountered an Error: ' . $this->vwsResponse->getStatus() . $this->vwsResponse->getBody();
        }
        if ($this->callCounter > 3 && $return) {
            $this->echoMessage .= var_dump($this->vwsResponse)." not fixed after trying 3 times.";
            $return = false;
        }
        return $return; // retry? or null == no error
    }

    /**
     *
     */
    private function errorPrevention()
    {
        // empty name filler
        if (empty($this->postData['t_name'])) {
            $this->postData['t_name'] = 'Anonymous Target '. random_int(1000000,9999999);
        }

        // image to jpg with 2000000byte limit
        $img = $this->postData['t_image'];
        $isJpg = (ord($img{0}) == 255)
            && (ord($img{1}) == 216)
            && (ord($img[strlen($img)-2]) == 255)
            && (ord($img[strlen($img)-1]) == 217);
        if (!$isJpg || strlen($img)) {
            $img = jpgAssistant($img, ['maxFileSize'=>2000000]);
            $this->postData['t_image'] = $img;
        }
    }

    /**
     * @param VuforiaAccessAPIException $e
     * @return bool
     */
    private function handleVAAPIE(VuforiaAccessAPIException $e):bool
    {
        if ($e->getCode() < 200) {
            switch ($e->getCode()) {
                case 110:
                    $this->postData['t_name'] = 'Anonymous Target';
                    break;
                case 111:
                    $this->postData['t_name'] = substr($this->postData['t_name'], 0, 60) . '...';
                    break;
                case 120:
                    $this->postData['t_image'] = toJPG($this->postData['t_image'],95);
                    break;
                default:
                    $this->echoMessage .= "Please contact a developer.\n
                                    While trying to prepare and send your request we encountered unrecognized Error $e->getCode() \n
                                    with the Message: $e->getMessage() at $e->getLine() in $e->getFile()";
                    return false;
            }
            return true;
        } else if ($e->getCode() > 299) {
            $this->echoMessage .= "Please contact a developer.\n
                            While trying to prepare and send your request we encountered Error $e->getCode() \n
                            with the Message: $e->getMessage() at $e->getLine() in $e->getFile()";
        } else { // User Input not valid?
            $this->echoMessage .= "Your request has not been sent! $e->getMessage()";
        }
        return false;
    }

    /**
     * @param HttpRequestException $e
     * @return bool
     * @throws HttpRequestException
     */
    private function handleHTTPRE(HttpRequestException $e)
    {
        throw $e; // Yep you can do that
        return false; // to calm PHP for the moment
    }

    /**
     * @param $tr_id
     * @param $t_id
     */
    private function logTransaction($tr_id, $t_id)
    {
        $sql = "INSERT INTO udvide.TransactionLog VALUES ($tr_id,?,$t_id)";
        access_DB::prepareExecuteGetStatement($sql, [$this->postData['username']]);
    }
}
