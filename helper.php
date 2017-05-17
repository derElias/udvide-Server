<?php
require_once 'access_DB.php';

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

/**
 * @param string $userPassHash
 * @param string $serverPassHash
 * @return bool
 */
function pepperedPassCheck(string $userPassHash,string $serverPassHash):bool
{ // ToDo Far Stretch goal redo validation to have cleaner code
    $keys = json_decode(file_get_contents('../keys.json'));
    return password_verify(sha1($userPassHash . $keys->pepper), $serverPassHash);
}

/**
 * @param string $user
 * @param string $pass
 * @return bool|array false on invalid login, integer if admin/client or array of targetIds
 */
function getPermissions(string $user, string $pass)
{
    $sql = <<<'SQL'
SELECT u.passHash, u.role, e.t_id
FROM udvide.Users u
JOIN Editors e
ON u.username = e.username
WHERE u.username = ?
SQL;
    $db = access_DB::prepareExecuteGetStatement($sql, $user); // Documentation: this is how to follow Don't trust the user with dbaccess in addition to purify
    if ($db === false)
        return false; // user doesn't exist
    if (!pepperedPassCheck($pass, $db[0]['passHash']))
        return false; // password incorrect
    if ($db[0]['role'] > 0)
        return [$db[0]['role']]; // returns role if not editor 1:admin 2:client
    $allMarkers = [];
    foreach ($db as $i=>$row) { // since each row is indexed with an integer (starting at 0) $i will just iterate
        $allMarkers[$i] = $row['t_id'];
    }
    return [0,$allMarkers]; // return an array like [0,['tid1','tid2']]
}
