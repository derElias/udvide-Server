<?php
require_once 'access_vfc.php';
require_once 'access_DB.php';
require_once 'helper.php';

echo handleForm();

function handleForm()
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") { // if form submit

        // structure
        // don't trust the client: purify data
        $postData = purifyUserData();
        // is login valid? (->1)
        // (t) is client or admin? (->2) (->editPerm)
        // (t/t) do post / delete
        // (t/f) has EditorLinks to Marker (->2)  (->editPerm)
        // (t/t) do put
        // (t/t) sync cached markers to db


        $perm = getPermissions($postData['username'], $postData['passHash']);
        if ($perm === false) {
            trigger_error('Invalid login (Bad password or username)');
            return 'Error';
        }

        $editPerm = $perm[0] > 0; // true when Admin or Client
        // post
        if ($postData['udvideVerb'] == 'POST' && $editPerm) { // ToDo Accept more variants? RFC!
            $vwsResponse = (new access_vfc())
                ->setTargetName($postData['t_name'])
                ->setImage($postData['t_image'])
                ->setMeta('/error.php?error=targetNotLinkedToDbYet')// ToDo create Page // redirect marker to Inform the marker still needs a moment (max 15 min according to API Doc)
                ->setActiveflag(false)// Target Inactive until DB synced
                ->execute('post');

            // ToDo: Error handling etc.

            $vwsResponseBody = json_decode($vwsResponse->getBody());

            // ToDo: Log Transaction IDs

            $t_id = $vwsResponseBody['target_id'];
            $username = $postData['username'];
            $sql = "INSERT INTO udvide.Targets (t_id,t_owner) VALUES ($t_id,?);";
            access_DB::prepareExecuteGetStatement($sql, [$username]);

            return 'Post successful';
        }

        // ToDo: delete

        // find edit permissions
        if (!$editPerm) { // true when Editor has Permissions
            foreach ($perm[1] as $tid) {
                $editPerm = $tid === $postData['t_id'];
                if ($editPerm)
                    break;
            }
        }

        if ($postData['access'] == 'PUT' && $editPerm) { // ToDo Accept more variants? RFC!
            $vwsa = new access_vfc();
            $vwsa->setTargetId($postData['t_id']);

            $updateVWS = false;
            if (isset($postData['t_name'])) {
                $vwsa->setTargetName($postData['t_name']);
                $updateVWS = true;
            }
            if (isset($postData['t_image'])) {
                $vwsa->setImage($postData['t_image']);
                $updateVWS = true;
            }
            if (isset($postData['activeFlag'])) {
                $vwsa->setActiveflag($postData['activeFlag']);
                $updateVWS = true;
            }

            if ($updateVWS) {
                $vwsResponse = $vwsa->execute('put');

                // ToDo: Error handling etc.

                $vwsResponseBody = json_decode($vwsResponse->getBody());

                // ToDo: Log Transaction IDs
            }

            $sql = "UPDATE udvide.Targets SET xpos = '?' WHERE t_id = ?;"; // ToDo
            access_DB::prepareExecuteGetStatement($sql, ['xpos', $postData['t_id']]);

            return 'Update Successful';
        }
        return 'Error: Invalid command?';
    } else {
        echo "This site is used to evaluate CRUD Form requests.\n
            Please use the form or consult the Documentation for more information";
        return 'Error';
    }
}
