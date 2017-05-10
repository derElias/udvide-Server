<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.05.2017
 * Time: 16:12
 */
function purifyUserData() {
    $result = [];
    foreach ($_POST as $item => $value) {
        $result[$item] = htmlspecialchars(stripslashes(trim($_POST[$item])));
    }
    return $result;
}
function pepperedPassCheck($userPassHash, $serverPassHash) { // ToDo Far Stretch goal redo validation to have cleaner code
    $keys = json_decode(file_get_contents('../keys.json'));
    password_verify(sha1($userPassHash . $keys->pepper), $serverPassHash);
}
function getPermissionsForUser($user) {
    $sql = <<<'SQL'
SELECT u.passHash, u.role, e.t_id
FROM udvide.Users u
JOIN Editors e
ON u.userID = e.userID
WHERE u.username = ?
SQL;
    return dba::prepareExecuteGetStatement($sql, $user); // Documentation: this is how to follow Don't trust the user with dbaccess in addition to purify
}