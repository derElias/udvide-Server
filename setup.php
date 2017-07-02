<?php
require_once 'vendor/autoload.php';

if (file_exists('MySQLDBDDL.sql')) {
    $sql = file_get_contents('MySQLDBDDL.sql');
    access_DB::prepareExecuteFetchStatement($sql);
    echo 'DB recreated';
}

$img = imagecreatefromstring(file_get_contents('img/img.jpg'));
$root_passwd = "imGoingToBePepperedAndSalted";
$default_password = "iAmBad";


//// manually setup root
$sql = <<<'SQL'
INSERT INTO udvide.Users (username,passHash,role)
VALUES (?,?,?)
SQL;
$dbpw = helper::pepperedPassGen($root_passwd);
access_DB::prepareExecuteFetchStatement($sql,['root',$dbpw,PERMISSIONS_ROOT]);
echo "root created! \n<br/>";
//// add devs as root
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
//// Add maps
$mapImg = imagecreatetruecolor(1000,1000);
(new map())
    ->setImage($mapImg)
    ->setName('test/1')
    ->create()
    ->setName('test/2')
    ->create();


////-----------------------------------------------------------------
// add test targets
$tAdmin = user::fromDB('test/tAdmin')
    ->setPassHash($default_password)
    ->login();
$targets[] = (new target())
    ->setName('test/t_01_admin')
    ->setImage($img)
    ->setActive(true)
    ->setContent('Hallo Welt!')
    ->setMap('test/1')
    ->setXPos(140)
    ->setYPos(70)
    ->setOwner($tAdmin->getUsername())
    ->create();
////
$targets[] = (new target())
    ->setName('test/t_02_client')
    ->setImage($img)
    ->setActive(true)
    ->setContent('<bold>Hallo Welt!</bold>')
    ->setMap('test/1')
    ->setXPos(150)
    ->setYPos(80)
    ->setOwner($tAdmin->getUsername())
    ->create();
////
$targets[] = (new target())
    ->setName('test/t_03_shared')
    ->setImage($img)
    ->setActive(true)
    ->setContent('<color red>Hallo Welt!</color>')
    ->setMap('test/2')
    ->setXPos(160)
    ->setYPos(90)
    ->setOwner($tAdmin->getUsername())
    ->create();
////
$target = target::fromDB('test/t_01_admin');
$user = user::fromDB('test/tEditor');
assignEditor($target,$user);
//*/
// PHP SET UP -----------------------------------------------
// BEGIN RESOURCE SETUP

// minify
$js = ""; //  todo get from udvide.js.php
minifyJS([$js, 'udvide.min.js']);
/*$css = ""; //  todo collect wherever it is (stretchgoal)
minifyCSS([$css, 'udvide.min.css']);*/



echo "DELETE setup.php BEFORE GOING LIVE!";
