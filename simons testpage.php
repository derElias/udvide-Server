<?php
require_once 'vendor/autoload.php';

echo time();
user::fromDB('root')->setPassHash('imGoingToBePepperedAndSalted')->login();
echo "\n<br/>" . user::getLoggedInUser()->getRole();
echo "\n<br/>";
echo "\n<br/>";

$t = target::fromDB("test/t_01_admin");
$fhwsPlugin = new fhwsApi();
$fhwsPlugin->userInput['RoomNbr'] = "H.1.1";
$fhwsPlugin->onTargetCreate($t);
$fhwsPlugin->onMobileRead($t);
echo $t->getContent();

