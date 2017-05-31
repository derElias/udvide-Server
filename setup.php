<?php
require_once 'helper.php';
require_once 'udvide.php';
$img = file_get_contents('img/img.jpg');
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
echo "root created! \n<br/>";
// add devs as root
$cu = new udvide();
$cu->createUser("dev/simon",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
$cu->createUser("dev/elias",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
$cu->createUser("dev/lukas",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
$cu->createUser("dev/niky",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
$cu->createUser("dev/siggi",$default_password,PERMISSIONS_DEVELOPER,'root',$new_users_password);
// add test ppl as root
$cu->createUser("test/tClient", $default_password,PERMISSIONS_CLIENT,'root',$new_users_password);
$cu->createUser("test/tAdmin", $default_password,PERMISSIONS_ADMIN,'root',$new_users_password);
$cu->createUser("test/tEditor", $default_password,PERMISSIONS_EDITOR,'root',$new_users_password);
echo "devs and test users created!\n<br/>";
// Add maps
$cu->
// add test targets
$user = 'test/tClient';
$pass = $default_password;
$targets[] = (new target())
    ->setName('test/t_01_client')
    ->setImage($img)
    ->setActive(true)
    ->setContent('{"text":"Hello World!"}')
    ->setMap('test/1')
    ->setXPos(140)
    ->setYPos(70)
    ->setOwner($user); // set owner defaults to the username provided via post header

$targets[] = (new target())
    ->setName('test/t_02_client')
    ->setImage($img)
    ->setActive(true)
    ->setContent('{"text":"Hello World! 2"}')
    ->setMap('test/1')
    ->setXPos(150)
    ->setYPos(80)
    ->setOwner('test/tAdmin');

$targets[] = (new target())
    ->setName('test/t_03_shared')
    ->setImage($img)
    ->setActive(true)
    ->setContent('{"text":"Hello World! 3"}')
    ->setMap('test/1')
    ->setXPos(160)
    ->setYPos(90)
    ->setOwner($user);

foreach ($targets as $target)
    $cu->doTargetManipulationAs('create',$target,$user,$pass);

$cu->assignEditorAs($targets[2], 'test/tEditor',$user,$pass);
