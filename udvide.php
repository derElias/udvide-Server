<?php
require_once 'helper.php'; // gets us also access to db, vfc and settings
if (file_exists('pluginLoader.php'))
    include_once 'pluginLoader.php';

class udvide
{
    /** @var  access_vfc */
    private $vwsRequest;

    /** @var  HTTP_Request2_Response */
    private $vwsResponse;

    private $callCounter = 0; // For Error handling
    private $echoMessage = '';

    private $prevIgnoreUserAbort;

    private $handlerResponse;
    private $forcePermissionReload;
    private $perm;
    private $pluginLoader;

    public function __construct()
    {
        // this stops users from aborting the script execution when the Vuforia Cloud and the Server are out of sync
        $this->prevIgnoreUserAbort = ignore_user_abort(true);

        $this->handlerResponse = new handlerResponse();
        $this->handlerResponse->success = false;
        
        if (class_exists('pluginLoader'))
            $this->pluginLoader = new pluginLoader();
    }

    public function __destruct()
    {
        ignore_user_abort($this->prevIgnoreUserAbort);
    }

    //<editor-fold desc="Target">
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

        $perm = $this->getPermissions($username, $passHash);
        // is login invalid? -> Error
        if ($perm === false) {
            $this->handlerResponse->message = 'Invalid login (Bad password or username)';
            return $this->handlerResponse;
        }

        $editPerm = $perm[0] > MIN_ALLOW_TARGET_CREATE-1;

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

        $editPerm = $perm[0] > MIN_ALLOW_TARGET_DEACTIVATE-1;
        if (!$editPerm && ALLOW_ASSIGNED_TARGET_DEACTIVATE && $verb == 'deactivate') {
            foreach ($perm[1] as $tid) {
                $editPerm = $tid === $target->id;
                if ($editPerm)
                    break;
            }
        }
        // deactivate if editPerm
        if ($verb == 'deactivate' && $editPerm) {
            $ct = $this->deactivateTarget($target,$username);
            if ($ct === true) {
                $this->handlerResponse->success = true;
            } else {
                $this->handlerResponse->message = $ct;
            }
            return $this->handlerResponse;
        }

        $editPerm = $perm[0] > MIN_ALLOW_TARGET_DELETE-1;
        if (!$editPerm && ALLOW_ASSIGNED_TARGET_DELETE && $verb == 'delete') {
            foreach ($perm[1] as $tid) {
                $editPerm = $tid === $target->id;
                if ($editPerm)
                    break;
            }
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

        $editPerm = $perm[0] > MIN_ALLOW_TARGET_UPDATE-1;
        // also grant editPerm if Editor for specified marker
        if (!$editPerm && ALLOW_ASSIGNED_TARGET_UPDATE && $verb == 'update') {
            foreach ($perm[1] as $tid) {
                $editPerm = $tid === $target->id;
                if ($editPerm)
                    break;
            }
        }
        // update if editPerm
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

    /**
     * @param target $target fixes some forbidden values
     */
    private function preProcessing(target &$target)
    {
        // empty name filler
        if (empty($target->name)) {
            $target->name = 'Anonymous Target '. random_int(1000000,9999999);
        }
        if (strlen($target->name) >= VUFORIA_TARGET_NAME_LIMIT) {
            $target->name = substr($target->name, 0, VUFORIA_TARGET_NAME_LIMIT-3) . '...';
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

        //sync vw_id
        $this->syncVWID($target);
    }

    /**
     * @param target $target
     */
    private function syncVWID(target &$target) {
        //sync vw_id
        $sql = 'SELECT vw_id FROM udvide.targets WHERE t_id = ?';
        $target->vw_id = access_DB::prepareExecuteFetchStatement($sql,[$target->id])[0]['vw_id'];
    }

    //<editor-fold desc="Target: Create Update">

    /**
     * creates a new target in the system
     * @param target $target adds the target id if successful
     * @param string $user
     * @return string|true
     * @throws HttpRequestException
     * @throws VuforiaAccessAPIException
     */
    private function createTarget(target &$target, string $user)
    {
        // create on DB
        $sql = 'INSERT INTO udvide.Targets (deleted,t_owner,xpos,ypos,map,content) VALUES (FALSE,?,?,?,?,?);';
        $exeValues = [
            isset($target->owner) ? $target->owner : null,
            isset($target->xPos) ? $target->xPos : null,
            isset($target->yPos) ? $target->yPos : null,
            isset($target->map) ? $target->map : null,
            isset($target->content) ? $target->content : null
        ];
        $target->id = access_DB::prepareExecuteStatementGetAffected($sql, $exeValues);

        // Create Target at VWS and handle potential errors
        do {
            $retry = false;
            try {
                $this->vwsRequest = (new access_vfc())
                    ->setTargetName($target->name)
                    ->setImage($target->image)
                    ->setMeta('/clientRequest.php?t=' . $target->id)
                    ->setActiveflag(isset($target->active) ? $target->active : true)
                    ->setAccessMethod('create');
                $this->vwsResponse = $this->vwsRequest->execute();
            } catch (VuforiaAccessAPIException $e) { // Errors we couldn't fix before creating and executing request, but detect before sending
                $sql = 'DELETE FROM udvide.Targets WHERE t_id = ?'; // manual revert ToDo t-sql?!?
                access_DB::prepareExecuteFetchStatement($sql,[$target->id]);
                throw $e;
            } catch (HttpRequestException $e) {
                $sql = 'DELETE FROM udvide.Targets WHERE t_id = ?';
                access_DB::prepareExecuteFetchStatement($sql,[$target->id]);
                throw $e;
                /*$retry = $this->handleHTTPRE($e);
                $this->callCounter++;
                if ($this->callCounter > 5) {
                    return var_dump($this->vwsRequest) . " not fixed after trying 5 times.";
                }*/
            }
        } while ($retry);

        // Handle potential semantic Errors
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
        $target->vw_id = $vwsResponseBody->target_id;
        $tr_id = $vwsResponseBody->transaction_id;

        logTransaction($tr_id,$user,$target->id);

        $sql = 'UPDATE udvide.Targets SET vw_id = ? WHERE t_id = ?;';
        access_DB::prepareExecuteFetchStatement($sql, [$target->vw_id, $target->id]);

        return true;
    }

    /**
     * updates a target to all set values
     * @param target $target
     * @param string $user
     * @param bool $isDeleting
     * @return string|true
     */
    private function updateTarget(target $target,string $user,bool $isDeleting = false)
    {
        do {
            $updateVWS = false;
            $retry = false;
            try {
                $vwsa = new access_vfc();
                $vwsa->setTargetId($target->vw_id) // as long as the client is programmed correctly this will work Docu
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
                if ($this->handleVAAPIE($e, $target)) { // ToDo use preProcessing()
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
            $tr_id = $vwsResponseBody->transaction_id;

            logTransaction($tr_id,$user,$target->id);
        }

        $updateDB = false;
        $sql = /** @lang text <- prevent IDE to hate us because it's not valid sql yet */
            'UPDATE udvide.Targets SET ';

        if (isset($target->deleted) && $isDeleting) {
            $sql .= " deleted = ? , ";
            $ins[] = $target->deleted;
            $updateDB = true;
        }

        if (isset($target->content)) {
            $sql .= " content = ? , ";
            $ins[] = $target->content;
            $updateDB = true;
        }

        if (isset($target->xPos)) {
            $sql .= " xPos = ? , ";
            $ins[] = $target->xPos;
            $updateDB = true;
        }

        if (isset($target->yPos)) {
            $sql .= " yPos = ? , ";
            $ins[] = $target->yPos;
            $updateDB = true;
        }

        if (isset($target->map)) {
            $sql .= " map = ? , ";
            $ins[] = $target->map;
            $updateDB = true;
        }

        if ($updateDB) {
            $sql .= "t_id = t_id WHERE t_id = ?;"; // end with a always-true&change-nothing t_id=t_id so we
                                                    //  do not have to take car of the , from previous ifs
            $ins[] = $target->id;
            access_DB::prepareExecuteFetchStatement($sql, $ins);
        }
        return true;
    }

    //</editor-fold>

    //<editor-fold desc="Target: Delete">
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
                $this->vwsRequest = (new access_vfc())
                    ->setTargetId($target->vw_id)
                    ->setAccessMethod('delete');
                $this->vwsResponse = $this->vwsRequest->execute();
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
        $tr_id = $vwsResponseBody->transaction_id;

        logTransaction($tr_id,$user,$target->id);

        $sql = 'DELETE FROM udvide.Targets WHERE t_id = ?';
        access_DB::prepareExecuteFetchStatement($sql,[$target->id]);

        return true;
    }

    /**
     * @param target $target
     * @param string $user
     * @return string|true
     */
    private function deactivateTarget(target &$target, string $user)
    {
        $target->setActive(false)->setDeleted(true);
        return $this->updateTarget($target,$user,true);
    }
    //</editor-fold>

    //<editor-fold desc="Target: Read">
    /**
     * @param string $user
     * @param string $password
     * @param int $page
     * @param int $pageSize
     * @return array|false
     * @throws PermissionException
     */
    public function getTargetPageByUser(string $user, string $password, int $page = 0, int $pageSize = 5)
    {
        if (empty($user))
            throw new PermissionException('Please Log in to view your targets!');
        $perm = $this->getPermissions($user,$password);
        if ($perm === false)
            throw new PermissionException(ERR_LOG01);

        $sql = <<<'SQL'
SELECT t.t_id, t.vw_id, t.t_owner, t.xPos, t.yPos, t.map, t.content
FROM udvide.Targets t
LEFT JOIN Editors e
ON t.t_id = e.t_id
WHERE e.username = ?
AND t.deleted = FALSE
ORDER BY t_id
LIMIT ?
OFFSET ?
SQL;
        $db = access_DB::prepareExecuteFetchStatement($sql, [$user, $pageSize, $page*$pageSize]);
        if ($db === false)
            return false; // no targets for $user
        $result = [];
        foreach ($db as $value) {
            $vw = json_decode((new access_vfc())
                ->setAccessMethod('summarize')
                ->setTargetId($value['vw_id'])
                ->execute()
                ->getBody());
            logTransaction($vw->transaction_id,$user,$value['t_id']);

            $value['t_name'] = $vw->target_name;
            $value['active'] = $vw->active_flag;
            $value['database'] = $vw->database_name;
            $value['track_rating'] = $vw->tracking_rating;
            $value['upl_date'] = $vw->upload_date;
            $value['recos_total'] = $vw->total_recos;
            $value['recos_this_month'] = $vw->current_month_recos;
            $value['recos_last_month'] = $vw->previous_month_recos;
            $result[] = $value;
        }
        return $result;
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
                    case 'TargetNameExist':
                        if (strlen($target->name) > 62) {
                            $this->echoMessage .= defined('ERR_VW111') ? ERR_VW111 : 'target name too long';
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
     * Handles Exceptions that occur before we send to VWS
     * @param VuforiaAccessAPIException $e
     * @param target $target
     * @return bool
     */
    private function handleVAAPIE(VuforiaAccessAPIException $e, target &$target):bool
    {
        if ($e->getCode() < 200) {
            $this->echoMessage .= "Please contact a developer.\n
                While trying to prepare and send your request we encountered unrecognized Error $e->getCode() \n
                with the Message: $e->getMessage() at $e->getLine() in $e->getFile(). They might ask you for this \n
                $e->getTraceAsString()";
            return false;
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
     * Handles connection problems
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
    //</editor-fold>

    //<editor-fold desc="User">
    //<editor-fold desc="User: Create">
    /**
     * @param string $new_users_name
     * @param string $new_users_password
     * @param int $new_users_role
     * @param string $username the username of the submitting person
     * @param string $password the password of the submitting person
     * @return bool
     * @throws Exception
     */
    public function createUser(string $new_users_name, string $new_users_password, int $new_users_role = PERMISSIONS_EDITOR,
                               string $username, string $password)
    {
        $perm = $this->getPermissions($username, $password)[0];
        if ($new_users_role >= $perm || $perm < MIN_ALLOW_USER_CREATE) {
            throw new Exception("Insufficient Permissions! you $username have permission level $perm");
        }
        $new_users_password = $this->pepperedPassGen($new_users_password);
        $sql= 'INSERT INTO udvide.Users VALUES (?,?,?,?)';
        access_DB::prepareExecuteFetchStatement($sql,[$new_users_name,false,$new_users_password,$new_users_role]);
        $this->forcePermissionReload = true;
        return true;
    }
    //</editor-fold>

    //<editor-fold desc="User: Read">
    /**
     * @param string $subject username
     * @param string $pass his pw
     * @return array|bool false on invalid login, integer if admin/client or array of targetIds
     */
    public function getPermissions(string $subject, string $pass)
    {
        if (isset($this->perm) && !$this->forcePermissionReload) {
            return $this->perm;
        }

        $sql = <<<'SQL'
SELECT u.passHash, u.deleted, u.role, e.t_id
FROM udvide.Users u
LEFT JOIN Editors e
ON u.username = e.username
LEFT JOIN Targets t
ON e.t_id = t.t_id
WHERE u.username = ?
AND t.deleted = FALSE
SQL;
        $db = access_DB::prepareExecuteFetchStatement($sql, [$subject]); // Don't trust the user!
        if ($db === false) {
            $this->perm = false;
            return false; // user doesn't exist
        }
        if ($db[0]['deleted'] === true) {
            $this->perm = false;
            return false; // user is marked for deletion
        }
        if (!$this->pepperedPassCheck($pass, $db[0]['passHash'])) {
            $this->perm = false;
            return false; // password incorrect
        }
        if ($db[0]['role'] > PERMISSIONS_EDITOR) {
            $this->perm = [$db[0]['role']];
            return [$db[0]['role']]; // returns role if not editor
        }
        $allMarkers = [];
        foreach ($db as $i=>$row) { // since each row is indexed with an integer (starting at 0) $i will just iterate
            $allMarkers[$i] = $row['t_id'];
        }
        $this->perm = [PERMISSIONS_EDITOR,$allMarkers];
        $this->forcePermissionReload = false;
        return [PERMISSIONS_EDITOR,$allMarkers]; // return an array like [1,['tid1','tid2']]
    }
    //</editor-fold>

    //<editor-fold desc="User: Update">

    //</editor-fold>

    //<editor-fold desc="User: Delete">
    /**
     * @param string $user
     * @param string $username
     * @param string $password
     * @return bool
     * @throws PermissionException
     */
    public function deleteUser(string $user, string $username, string $password)
    {
        $perm = $this->getPermissions($username, $password)[0];
        if ($perm === false) {
            throw new PermissionException('Please log in to do that!');
        }
        if ($user !== $username && $perm < MIN_ALLOW_USER_DELETE)
            throw new PermissionException("Insufficient Permissions to delete $user!");

        $sql = 'DELETE FROM udvide.Users WHERE username = ?';
        access_DB::prepareExecuteFetchStatement($sql,[$user]);
        $this->forcePermissionReload = true;
        return true;
    }

    public function deactivateUser(string $user, string $username, string $password)
    {
        $perm = $this->getPermissions($username, $password)[0];
        if ($perm === false) {
            throw new PermissionException('Please log in to do that!');
        }
        if ($user !== $username && $perm < MIN_ALLOW_USER_DEACTIVATE)
            throw new PermissionException("Insufficient Permissions to delete $user!");

        // ToDo updateUser()
        $this->forcePermissionReload = true;
        return true;
    }
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="Map">
    //<editor-fold desc="Map: Create">
    /**
     * @param string $name
     * @param string|resource $img
     * @param string $username
     * @param string $passHash
     * @return bool indicates success
     * @throws PermissionException
     */
    public function createMap(string $name, $img, string $username, string $passHash):bool
    {
        if ($this->getPermissions($username,$passHash)[0] < MIN_ALLOW_MAP_CREATE) {
            throw new PermissionException('Insufficient Permissions to create Map!');
        }
        if (is_string($img)) {
            $img = imagecreatefromstring($img);
        }
        $img = imagescale($img,MAP_WIDTH);
        $img = jpgAssistant($img,['quality'=>95]);
        $sql = 'INSERT INTO udvide.Maps VALUES (?,?)';
        access_DB::prepareExecuteFetchStatement($sql,[$name,$img]);
        return true;
    }
    //</editor-fold>

    //<editor-fold desc="Map: Read">
    /**
     * @param string $name
     * @return string
     */
    public function readMap(string $name):string
    {
        $sql = 'SELECT image FROM udvide.Maps WHERE name = ?';
        return access_DB::prepareExecuteFetchStatement($sql,[$name])[0]['image'];
    }
    //</editor-fold>

    //<editor-fold desc="Map: Update">
    /**
     * @param string $name
     * @param $img
     * @param string $username
     * @param string $passHash
     * @param string|null $newName
     * @return bool
     * @throws PermissionException
     */
    public function updateMap(string $name, $img, string $username, string $passHash, string $newName = null):bool
    {
        if ($this->getPermissions($username,$passHash)[0] < MIN_ALLOW_MAP_UPDATE) {
            throw new PermissionException('Insufficient Permissions to create Map!');
        }
        if (!is_null($img)) {
            if (is_string($img)) {
                $img = imagecreatefromstring($img);
            }
            $img = imagescale($img, MAP_WIDTH);
            $img = jpgAssistant($img, ['quality' => 95]);
            $insertImage = '?';
        } else {
            $insertImage = 'image';
        }
        if (is_null($newName))
            $newName = $name;
        $sql = "UPDATE udvide.Maps SET name = ?, image = $insertImage WHERE name = ?";
        access_DB::prepareExecuteFetchStatement($sql,$insertImage === '?' ? [$newName,$img,$name] : [$newName,$name]);
        return true;
    }
    //</editor-fold>

    //<editor-fold desc="Map: Delete">
    /**
     * @param string $name
     * @param string $username
     * @param string $passHash
     * @return bool
     * @throws PermissionException
     */
    public function deleteMap(string $name, string $username, string $passHash)
    {
        if ($this->getPermissions($username,$passHash)[0] < MIN_ALLOW_MAP_DELETE) {
            throw new PermissionException('Insufficient Permissions to create Map!');
        }
        $sql = 'DELETE FROM udvide.Maps WHERE name = ?';
        access_DB::prepareExecuteFetchStatement($sql,[$name]);
        return true;
    }
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="Assign">
    /**
     * @param string|target $targetIdentifier
     * @param string $user
     * @param string $username the username of the submitting person
     * @param string $password the password of the submitting person
     * @throws Exception
     */
    public function assignEditorAs($targetIdentifier, string $user,
                                   string $username, string $password)
    {
        if ($targetIdentifier instanceof target)
            $targetIdentifier = $targetIdentifier->id;

        if ($this->getPermissions($username, $password)[0] < MIN_ALLOW_TARGET_ASSIGN)
            throw new Exception("Insufficient Permissions to make $user an Editor of $targetIdentifier!");

        $sql = 'INSERT INTO udvide.Editors VALUES (?,?)';
        access_DB::prepareExecuteFetchStatement($sql,[$targetIdentifier,$user]);
    }
    //</editor-fold>

    /**
     * Compares a sent in passHash (password) with a peppered and salted passHash
     * @param string $userPassHash the sent password value (should be hashed client-side)
     * @param string $serverPassHash the db stored password
     * @return bool
     */
    private function pepperedPassCheck(string $userPassHash,string $serverPassHash):bool
    {
        $keys = json_decode(file_get_contents('keys.json'));
        return password_verify(sha1($userPassHash . $keys->pepper), $serverPassHash);
    }

    /**
     * generates a peppered and salted passHash
     * @param string $new_users_password
     * @return bool|string
     */
    private function pepperedPassGen(string $new_users_password)
    {
        $keys = json_decode(file_get_contents('keys.json'));
        return password_hash(sha1($new_users_password . $keys->pepper), PASSWORD_DEFAULT);
    }
}
