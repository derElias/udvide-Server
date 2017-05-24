<?php
require_once 'helper.php';

$new_users_password = "imGoingToBePepperedAndSalted";
$default_password = "iAmBad";
// manually setup root
$sql = <<<'SQL'
INSERT INTO udvide.Users
VALUES (?,?,?)
SQL;
$keys = json_decode(file_get_contents('keys.json'));
$new_users_peppered_salted_password = password_hash(sha1($new_users_password . $keys->pepper),PASSWORD_DEFAULT); // ToDo check
access_DB::prepareExecuteFetchStatement($sql,['root',$new_users_peppered_salted_password,PERMISSIONS_ROOT]);
echo 'root created!';
// add devs as root
addUser("dev/simon",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
addUser("dev/elias",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
addUser("dev/lukas",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
addUser("dev/niky",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
addUser("dev/siggi",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
// add test ppl as root
addUser("test/tClient", $default_password,PERMISSIONS_CLIENT,'root',$new_users_password);
addUser("test/tAdmin", $default_password,PERMISSIONS_ADMIN,'root',$new_users_password);
addUser("test/tEditor", $default_password,PERMISSIONS_EDITOR,'root',$new_users_password);
echo 'devs and test users created!';
// add test targets

// assign editors to targets

