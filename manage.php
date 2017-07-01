<?php
$template = file_get_contents('templates/manage.html');

$temp = file_get_contents('templates/header.html');
$template = str_replace('<!--header-->',$temp,$template);

$temp = file_get_contents('templates/entrytableAdmin.html');
$template = str_replace('<!--content-->',$temp,$template);



$temp = file_get_contents('templates/footer.html');
$template = str_replace('<!--footer-->',$temp,$template);
echo $template;
/*
require_once 'acces_DB.php';
$new_users_password = "imGoingToBePepperedAndSalted";
$default_password = "iAmBad";

// manually setup root
$sql = <<<'SQL'
INSERT INTO udvide.Users (username,passHash,role)
VALUES (?,?,?)
SQL;
$keys = json_decode(file_get_contents('keys.json'));
$new_users_peppered_salted_password = password_hash(sha1($new_users_password . $keys->pepper),PASSWORD_DEFAULT);
access_DB::prepareExecuteFetchStatement($sql,['root',$new_users_peppered_salted_password,PERMISSIONS_ROOT]);

?>
*/