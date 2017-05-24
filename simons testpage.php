<?php
require_once 'helper.php';

echo time();

$sql = <<<'SQL'
SELECT u.passHash, u.role, e.t_id
FROM udvide.Users u
LEFT JOIN Editors e
ON u.username = e.username
WHERE u.username = ?
SQL;
$db = access_DB::prepareExecuteFetchStatement($sql,['root']);
var_dump($db);
echo "\n <br/>";
$testpass = 'imGoingToBePepperedAndSalted';
echo $testpass . "\n <br/>";
$shouldBeDB = password_hash(sha1($new_users_password . $keys->pepper));

echo $testpass . "\n <br/>";

$ppcheck = pepperedPassCheck($testpass,'32f13fc05b42b586bf42045fcd065fd3ba33699b');
$e = getTestPermissions('root','32f13fc05b42b586bf42045fcd065fd3ba33699b');
var_dump($e);

/**
 * @param string $user
 * @param string $pass
 * @return bool|array false on invalid login, integer if admin/client or array of targetIds
 */
function getTestPermissions(string $user, string $pass)
{
    $sql = <<<'SQL'
SELECT u.passHash, u.role, e.t_id
FROM udvide.Users u
LEFT JOIN Editors e
ON u.username = e.username
WHERE u.username = ?
SQL;
    $db = access_DB::prepareExecuteFetchStatement($sql, [$user]); // Documentation: this is how to follow Don't trust the user with dbaccess in addition to purify
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
