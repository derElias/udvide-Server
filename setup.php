<?php
require_once 'udvideV3.php';
$img = file_get_contents('img/img.jpg');
$root_passwd = "imGoingToBePepperedAndSalted";
$default_password = "iAmBad";

// manually setup root
$sql = <<<'SQL'
INSERT INTO udvide.Users (username,passHash,role)
VALUES (?,?,?)
SQL;
$dbpw = pepperedPassGen($root_passwd);
access_DB::prepareExecuteFetchStatement($sql,['root',$dbpw,PERMISSIONS_ROOT]);
echo "root created! \n<br/>";
// add devs as root
$root = user::fromDB('root')
    ->setPassHash($root_passwd)
    ->login();

(new user())
    ->setPassHash($default_password)
    ->setRole(PERMISSIONS_DEVELOPER)
    ->setUsername("dev/simon")
    ->create()
    ->setUsername("dev/elias")
    ->create()
    ->setUsername("dev/lukas")
    ->create()
    ->setUsername("dev/niky")
    ->create()
    ->setUsername("dev/siggi")
    ->create()
    // add test ppl as root
    ->setRole(PERMISSIONS_CLIENT)
    ->setUsername("test/tClient")
    ->create()
    ->setRole(PERMISSIONS_ADMIN)
    ->setUsername("test/tAdmin")
    ->create()
    ->setRole(PERMISSIONS_EDITOR)
    ->setTargetCreateLimit(5)
    ->setUsername("test/tEditor")
    ->create();
echo "devs and test users created!\n<br/>";
// Add maps
$mapImg = imagecreatetruecolor(1000,1000);
(new map())
    ->setImage($mapImg)
    ->setName('test/1')
    ->create()
    ->setName('test/2')
    ->create();


/*/-----------------------------------------------------------------
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
//*/
