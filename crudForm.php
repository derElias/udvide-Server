<?php
require_once 'access_vfc.php';
require_once 'access_DB.php';
require_once 'helper.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") { // if form submit
    echo '<!DOCTYPE html><html><head></head><body>';
    $handlerResponse = (new crudFormHandler())->handleForm();
    echo '<p>In future versions the software will handle the form async and not replace the page</p></br>';
    if ($handlerResponse->success) {
        echo '<p>';
        echo $handlerResponse->message;
        echo '</br>';
        echo $handlerResponse->t_id;
        echo '</p>';
    } else {
        echo '<div class="popMessage"><p>';
        echo $handlerResponse->message;
        echo '</p></div>';
    }
echo '</body></html>';
} else {
    echo "This site is used to evaluate CRUD Form requests.\n
                Please use the form or consult the Documentation for more information";
}

class crudFormHandler
{
    /** @var  access_vfc */
    private $vwsRequest;

    /** @var  HTTP_Request2_Response */
    private $vwsResponse;

    private $callCounter = 0;
    private $echoMessage = '';

    private $prevIgnoreUserAbort;

    private $handlerResponse;

    public function __construct()
    {
        // this stops users from aborting the script execution when the Vuforia Cloud and the Server are out of sync
        $this->prevIgnoreUserAbort = ignore_user_abort(true);

        $this->handlerResponse = new handlerResponse();
        $this->handlerResponse->success = false;
    }

    public function __destruct()
    {
        ignore_user_abort($this->prevIgnoreUserAbort);
    }

    /**
     * @return handlerResponse
     */
    public function handleForm()
    {
        $cleanData = purifyUserData();
        $target = $this->arrayToTarget($cleanData);
        $verb = $cleanData['udvideVerb'];
        $user = $cleanData['username'];
        $passH = $cleanData['passHash'];
        return $this->doTargetManipulationAs($verb, $target, $user, $passH);
    }

    /**
     * checks login and then calls a CrUD function from below
     * @param string $verb
     * @param target $target adds id when creating
     * @param string $username
     * @param string $passHash
     * @return handlerResponse
     */
    public function doTargetManipulationAs(string $verb, target &$target, string $username, string $passHash)
    {

        // streamline input
        $this->preProcessing($target);
        $verb = mb_strtolower($verb);

        $perm = getPermissions($username, $passHash);
        // is login invalid? -> Error
        if ($perm === false) {
            $this->handlerResponse->message = 'Invalid login (Bad password or username)';
            return $this->handlerResponse;
        }

        // is client or admin? (->editPerm)
        $editPerm = $perm[0] > 0; // true when Admin or Client (or Dev or Root)

        // create if $editPerm
        if ($verb == 'create' && $editPerm) {
            if (empty($target->owner))
                $target->owner = $username;
            $ct = $this->createTarget($target,$username);
            if ($ct === true) {
                $this->handlerResponse->success = true;
                $this->handlerResponse->t_id = $target->id;
            } else {
                $this->handlerResponse->message = $ct;
            }
            return $this->handlerResponse;
        }

        // delete if editPerm
        if ($verb == 'delete' && $editPerm) {
            $ct = $this->deleteTarget($target,$username);
            if ($ct === true) {
                $this->handlerResponse->success = true;
            } else {
                $this->handlerResponse->message = $ct;
            }
            return $this->handlerResponse;
        }

        // also grant editPerm if Editor for specified marker
        if (!$editPerm) {
            foreach ($perm[1] as $tid) {
                $editPerm = $tid === $target->id;
                if ($editPerm)
                    break;
            }
        }

        if ($verb == 'update' && $editPerm) {
            $ct = $this->updateTarget($target,$username);
            if ($ct === true) {
                $this->handlerResponse->success = true;
            } else {
                $this->handlerResponse->message = $ct;
            }
            return $this->handlerResponse;
        }
        $this->handlerResponse->message = 'Invalid command?';
        return $this->handlerResponse;
    }

    //<editor-fold desc="Create Update Delete">

    /**
     * creates a new target in the system
     * @param target $target adds the target id if successful
     * @param string $user
     * @return string|true
     */
    private function createTarget(target &$target, string $user)
    {
        do {
            $retry = false;
            try {
                $this->vwsRequest = (new access_vfc())
                    ->setTargetName($target->name)
                    ->setImage($target->image)
                    ->setMeta('/error.php?error=targetNotLinkedToDbYet')// redirect marker to Inform the marker still needs a moment (max 15 min according to API Doc)
                    ->setActiveflag(false)// Target Inactive until DB synced
                    ->setAccessMethod('create');
                $this->vwsResponse = $this->vwsRequest->execute();
            } catch (VuforiaAccessAPIException $e) { // Errors we can potentially fix before the request is actually sent out
                if ($this->handleVAAPIE($e, $target)) {
                    $this->vwsResponse = $this->vwsRequest->execute();
                } else {
                    return $this->echoMessage;
                }
            } catch (HttpRequestException $e) {
                $retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    return var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                }
            }
        } while ($retry);

        $this->callCounter = 0;
        $re = $this->errorHandleVFResponse($target);
        while ($re === true) {
            $this->vwsResponse = $this->vwsRequest->execute();
            $re = $this->errorHandleVFResponse($target);
        }
        if ($re === false) {
            return "Please review your input! " . $this->echoMessage;
        }

        $vwsResponseBody = json_decode($this->vwsResponse->getBody());
        $t_id = $vwsResponseBody->target_id;
        $target->id = $t_id;
        $tr_id = $vwsResponseBody->transaction_id;

        // Sync DBs and insert our own stuff
        $sql = "INSERT INTO udvide.Targets (t_id,t_owner,xpos,ypos,map) VALUES (?,?,?,?,?);";
        access_DB::prepareExecuteFetchStatement($sql, [$t_id,$target->owner, $target->xPos, $target->yPos, $target->map]);

        logTransaction($tr_id,$user,$t_id);

        // Update VF DB to the now named target page
        do {
            $retry = false;
            try {
                $this->vwsRequest = (new access_vfc())
                    ->setTargetId($t_id)
                    ->setMeta("/clientRequest.php?t=$t_id")
                    ->setActiveflag($target->active)
                    ->setAccessMethod('update');
                $this->vwsResponse = $this->vwsRequest->execute();
            } catch (VuforiaAccessAPIException $e) { // Errors we can potentially fix before the request is actually sent out
                if ($this->handleVAAPIE($e, $target)) {
                    $this->vwsResponse = $this->vwsRequest->execute();
                } else {
                    return $this->echoMessage;
                }
            } catch (HttpRequestException $e) {
                $retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    return var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                }
            }
        } while($retry);

        $this->callCounter = 0;
        $re = $this->errorHandleVFResponse($target);
        while ($re === true) {
            $this->vwsResponse = $this->vwsRequest->execute();
            $re = $this->errorHandleVFResponse($target);
        }
        if ($re === false) {
            return "Please review your input! " . $this->echoMessage;
        }
        $vwsResponseBody = json_decode($this->vwsResponse->getBody());
        $t_id = $vwsResponseBody->target_id;
        $tr_id = $vwsResponseBody->transaction_id;
        logTransaction($tr_id,$user,$t_id);
        $target->id = $t_id;
        return true;
    }

    /**
     * updates a target to all set values
     * @param target $target
     * @param string $user
     * @return string|true
     */
    private function updateTarget(target $target,string $user)
    {
        do {
            $updateVWS = false;
            $retry = false;
            try {
                $vwsa = new access_vfc();
                $vwsa->setTargetId($target->id)
                    ->setAccessMethod('update');

                if (isset($target->name)) {
                    $vwsa->setTargetName($target->name);
                    $updateVWS = true;
                }
                if (isset($target->image)) {
                    $vwsa->setImage($target->image);
                    $updateVWS = true;
                }
                if (isset($target->active)) {
                    $vwsa->setActiveflag($target->active);
                    $updateVWS = true;
                }

                if ($updateVWS) {
                    $this->vwsResponse = $vwsa->execute();
                }
            } catch (VuforiaAccessAPIException $e) {
                if ($this->handleVAAPIE($e, $target)) {
                    $this->vwsResponse = $this->vwsRequest->execute();
                } else {
                    return $this->echoMessage;
                }
            } catch (HttpRequestException $e) {
                $retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    return var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                }
            }
        } while ($retry);

        $this->callCounter = 0;
        $re = $this->errorHandleVFResponse($target);
        while ($re === true) {
            $this->vwsResponse = $this->vwsRequest->execute();
            $re = $this->errorHandleVFResponse($target);
        }
        if ($re === false) {
            return "Please review your input! " . $this->echoMessage;
        }

        if ($updateVWS) {
            $vwsResponseBody = json_decode($this->vwsResponse->getBody());
            $t_id = $vwsResponseBody->target_id;
            $tr_id = $vwsResponseBody->transaction_id;

            logTransaction($tr_id,$user,$t_id);
        }

        $updateDB = false;
        $sql = /** @lang text */
            "UPDATE udvide.Targets SET ";

        if (isset($target->xPos)) {
            $sql .= " xPos = '?' , ";
            $ins[] = $target->xPos;
            $updateDB = true;
        }

        if (isset($target->yPos)) {
            $sql .= " yPos = '?' , ";
            $ins[] = $target->yPos;
            $updateDB = true;
        }

        if (isset($target->map)) {
            $sql .= " map = '?' , ";
            $ins[] = $target->map;
            $updateDB = true;
        }

        if ($updateDB) {
            $sql .= "t_id = t_id WHERE t_id = '?';";
            $ins[] = $target->id;
            access_DB::prepareExecuteFetchStatement($sql, $ins);
        }
        return true;
    }

    /**
     * deletes a Target based on its id from the whole system
     * @param target $target
     * @param string $user
     * @return string|true
     */
    private function deleteTarget(target $target, string $user)
    {
        do {
            $retry = false;
            try {
                $vwsa = new access_vfc();
                $vwsa->setTargetId($target->id)
                    ->setAccessMethod('delete');
            } catch (VuforiaAccessAPIException $e) {
                if ($this->handleVAAPIE($e, $target)) {
                    $this->vwsResponse = $this->vwsRequest->execute();
                } else {
                    return $this->echoMessage;
                }
            } catch (HttpRequestException $e) {
                $retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    return var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                }
            }
        } while ($retry);

        $this->callCounter = 0;
        $re = $this->errorHandleVFResponse($target);
        while ($re === true) {
            $this->vwsResponse = $this->vwsRequest->execute();
            $re = $this->errorHandleVFResponse($target);
        }
        if ($re === false) {
            return "Please review your input! " . $this->echoMessage;
        }

        $vwsResponseBody = json_decode($this->vwsResponse->getBody());
        $t_id = $vwsResponseBody->target_id;
        $tr_id = $vwsResponseBody->transaction_id;

        logTransaction($tr_id,$user,$t_id);

        $sql = 'DELETE FROM udvide.Targets WHERE t_id = ?';
        access_DB::prepareExecuteFetchStatement($sql,$target->id);

        return true;
    }
    //</editor-fold>

    //<editor-fold desc="Error Handler">
    /**
     * @param target $target
     * @return bool|null
     */
    private function errorHandleVFResponse(target &$target)
    {
        $this->callCounter++;
        $return = false;

        switch ($this->vwsResponse->getStatus()) {
            case 200: // ok
            case 201: // TargetCreated
                return null; // no error
            case 403:
                switch (json_decode($this->vwsResponse->getBody())->result_code) {
                    case 'TargetNameExists':
                        if (strlen($target->name) > 62) {
                            $this->echoMessage .= 'Image has been deleted from other source.';
                            $return = false;
                        } else {
                            $target->name = $target->name . ' 2';
                            $this->vwsRequest->setTargetName($target->name);
                            $return = true;
                        }
                        break;
                }
                break;
            case 404: // UnknownTarget
                $this->echoMessage .= 'Image has been deleted from other source.';
                $return = false;
                break;
            case 422:
                switch (json_decode($this->vwsResponse->getBody())->result_code) {
                    case 'BadImage':
                        $this->echoMessage .= 'Image seems bad. please submit only in a <a href="wiki.php?a=supportedImageFormats">supported format</a>';
                        $return = false;
                        break;
                    case 'ImageTooLarge':
                        $this->echoMessage .= 'Image seems too large and we could not fix it automatically.';
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
     * @param VuforiaAccessAPIException $e
     * @param target $target
     * @return bool
     */
    private function handleVAAPIE(VuforiaAccessAPIException $e, target &$target):bool
    {
        if ($e->getCode() < 200) {
            switch ($e->getCode()) {
                case 110:
                    $target->name = 'Anonymous Target';
                    break;
                case 111:
                    $target->name = substr($target->name, 0, 60) . '...';
                    break;
                case 120:
                    // shouldn't happen
                    $target->image = jpgAssistant($target->image,['quality'=>95]);
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
     * @param Exception $e
     * @return bool
     * @throws Exception
     */
    private function handleHTTPRE(Exception $e)
    {
        throw $e; // Yep you can do that
        // ToDo
        return false; // to calm PHP for the moment
    }
    //</editor-fold>

    //<editor-fold desc="Utility">
    /**
     * Does preventive stuff on $postData
     * @param target $target fixes some forbidden values
     */
    private function preProcessing(target &$target)
    {
        // empty name filler
        if (empty($target->name)) {
            $target->name = 'Anonymous Target '. random_int(1000000,9999999);
        }

        // image to jpg with 2000000byte limit
        $img = $target->image;
        if (is_string($img)) {
            $isJpg = (ord($img{0}) == 255)
                && (ord($img{1}) == 216)
                && (ord($img[strlen($img) - 2]) == 255)
                && (ord($img[strlen($img) - 1]) == 217);
            if (!$isJpg || strlen($img) > VUFORIA_DATA_SIZE_LIMIT) { // its not a jpg or its too large so we convert it
                $img = jpgAssistant($img, ['maxFileSize' => VUFORIA_DATA_SIZE_LIMIT]);
            }
        } elseif (imgJpgSize($img,95) > VUFORIA_DATA_SIZE_LIMIT) {
            $img = jpgAssistant($img, ['maxFileSize' => VUFORIA_DATA_SIZE_LIMIT]);
        } else {
            imgResToJpgString($img,95);
        }
        $target->image = $img;
    }

    /**
     * @param array $postData
     * @return target
     */
    public function arrayToTarget(array $postData):target
    {
        $response = new target();
        if (isset($postData['activeFlag'])) {
            $response->active = $postData['activeFlag'];
        }
        if (isset($postData['map'])) {
            $response->map = $postData['map'];
        }
        if (isset($postData['yPos'])) {
            $response->yPos = $postData['yPos'];
        }
        if (isset($postData['xPos'])) {
            $response->xPos = $postData['xPos'];
        }
        if (isset($postData['username'])) {
            $response->owner = $postData['username'];
        }
        if (isset($postData['t_image'])) {
            $response->image = $postData['t_image'];
        }
        if (isset($postData['t_name'])) {
            $response->name = $postData['t_name'];
        }
        if (isset($postData['t_id'])) {
            $response->id = $postData['t_id'];
        }
        if (isset($postData['content'])) {
            $response->content = $postData['content'];
        }
        return $response;
    }
    //</editor-fold>

}

class handlerResponse {
    /** @var  bool */
    public $success;
    /** @var  string */
    public $message;
    /** @var  int */
    public $t_id;
}

class target {
    /** @var  string */
    public $name;
    /** @var  string|resource */
    public $image;
    /** @var  bool */
    public $active;
    /** @var  string */
    public $content;
    /** @var  string */
    public $owner;
    /** @var  int */
    public $xPos;
    /** @var  int */
    public $yPos;
    /** @var  string */
    public $map;
    /** @var  string */
    public $id;

    //<editor-fold desc="Fluent Setters (set null if omitted param)">
    /**
     * @param string $name
     * @return target
     */
    public function setName(string $name = null): target
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param resource|string $image
     * @return target
     */
    public function setImage($image = null)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param bool $active
     * @return target
     */
    public function setActive(bool $active = null): target
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @param string $content
     * @return target
     */
    public function setContent(string $content = null): target
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param string $owner
     * @return target
     */
    public function setOwner(string $owner = null): target
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @param int $xPos
     * @return target
     */
    public function setXPos(int $xPos = null): target
    {
        $this->xPos = $xPos;
        return $this;
    }

    /**
     * @param int $yPos
     * @return target
     */
    public function setYPos(int $yPos = null): target
    {
        $this->yPos = $yPos;
        return $this;
    }

    /**
     * @param string $map
     * @return target
     */
    public function setMap(string $map = null): target
    {
        $this->map = $map;
        return $this;
    }

    /**
     * @param string $id
     * @return target
     */
    public function setId(string $id = null): target
    {
        $this->id = $id;
        return $this;
    }
    //</editor-fold>
}
