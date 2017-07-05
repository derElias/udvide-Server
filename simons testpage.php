<?php
require_once 'vendor/autoload.php';

/*
echo time();
user::fromDB('root')->setPassHash('imGoingToBePepperedAndSalted')->login();
echo "\n<br/>" . user::getLoggedInUser()->getRole();
echo "\n<br/>";
echo "\n<br/>";
*/

/*
$t = target::fromDB("test/t_01_admin");
$fhwsPlugin = new fhwsApi();
$fhwsPlugin->userInput['RoomNbr'] = "H.1.1";
$fhwsPlugin->onTargetCreate($t);
$fhwsPlugin->onMobileRead($t);
echo $t->getContent();
*/
$sql = <<<'SQL'
SELECT name, image FROM udvide.Maps
SQL;

$db = access_DB::prepareExecuteFetchStatement($sql);
$sql = <<<'SQL'
UPDATE udvide.Maps SET image = ? WHERE name = ?
SQL;

foreach ($db as $ind => $row) {
    $ins = ["data:image/jpeg;base64," . base64_encode($row['image']),$ind];
    access_DB::prepareExecuteFetchStatement($sql,$ins);
}

