<?php
require_once 'udvide.php';

echo time();
user::fromDB('root')->setPassHash('imGoingToBePepperedAndSalted')->login();
echo "\n<br/>" . user::getLoggedInUser()->getRole();

