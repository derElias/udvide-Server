<?php
require_once 'vfcAccess.php';
require_once 'dbaccess/dbaUdv.php';
require_once 'helper.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") { // if form submit

    $postData = purifyUserData();

    $perm = getPermissionsForUser($postData['username']);

    $loginOk = $perm !== false // user exists?
        && pepperedPassCheck($postData['passHash'], $perm[0]['passHash']);
    if ($loginOk) { //login valid
        if ($postData['udvideVerb'] == 'POST') { // ToDo Accept more variants? RFC!
            if ($perm[0]['role'] > 0) { //login has req permissions (Admin(1) or Client(2))
                $vwsResponse = (new vfcAccess())
                    ->setTargetName($postData['t_name'])
                    ->setWidth($postData['t_width'])
                    ->setImage($postData['t_image'])
                    ->setMeta('/error.php?error=targetNotLinkedToDbYet') // ToDo create Page // redirect marker to Inform the marker still needs a moment (max 15 min according to API Doc)
                    ->setActiveflag(false) // Target Inactive until DB synced
                    ->execute('post');

                // ToDo: Error handling etc.

                $vwsResponseBody = json_decode($vwsResponse->getBody());
                $t_id = $vwsResponseBody['target_id'];
                $username = $postData['username'];
                echo $t_id;
                $sql = "INSERT INTO udvide.Targets (t_id,t_owner) VALUES ($t_id,$username);";
                dba::prepareExecuteGetStatement($sql);

            } else {
                echo "You seem to have insufficient Permissions to Create a new file!\n
                The Login you are using has Editor permissions;\n
                if you have Credentials to Log into a Admin or Client Account,
                please do so <a href='manage.php?login=false&redirect=" . basename($_SERVER['SCRIPT_FILENAME']) . "'>here</a>"; //ToDo Error messages feel out of place here... //ToDo redir untested/nimmt keine getvariablen mit um die anfrage zu wiederholen
            }
        }
        if ($postData['access'] == 'PUT') { // ToDo Accept more variants? RFC!
            $editPerm = $perm[0]['role'] > 0; // true when Admin or Client
            if (!$editPerm) { // true when Editor has Permissions
                foreach ($perm as $row) {
                    $editPerm = $row['t_id'] === $postData['t_id'];
                    if ($editPerm)
                        break;
                }
            }
            // ToDo: stuff
        }
    }


} else {
    echo "This site is used to evaluate CRUD Form requests.\n
     Please use the form or consult the Documentation for more information";
}
