<?php
require_once 'vendor/autoload.php';

/* // Apparently this doesn't work right now... i have no idea why but nothing else seems affected
if (file_exists('MySQLDBDDL.sql')) {
    $sql = file_get_contents('MySQLDBDDL.sql');
    access_DB::prepareExecuteFetchStatement($sql);
    echo 'DB recreated';
}*/

$img = imagecreatefromstring(file_get_contents('img/SampleGen.jpg'));
$img2 = imagecreatefromstring(file_get_contents('img/SampleGen2.jpg'));
$root_passwd = "plzHackMe";
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
    /*->setRole(PERMISSIONS_DEVELOPER)
    ->setUsername("dev/simon")
    ->create()
    ->setUsername("dev/elias")
    ->create()
    ->setUsername("dev/lukas")
    ->create()
    ->setUsername("dev/niky")
    ->create()
    ->setUsername("dev/siggi")
    ->create()*/
    // add test ppl as root
    ->setRole(PERMISSIONS_ADMIN)
    ->setUsername("BBT/Admin")
    ->create()
    ->setRole(PERMISSIONS_EDITOR)
    ->setTargetCreateLimit(5)
    ->setUsername("BBT/Editor")
    ->create();
echo "devs and test users created!\n<br/>";
//// Add maps
$mapImg = imagecreatetruecolor(100,100);
imagefilledrectangle($mapImg,5,5,95,95,imagecolorallocate($mapImg,200,255,200));
(new map())
    ->setImage($mapImg)
    ->setName('initialMap')
    ->create()
    ->setName('BBT/m1')
    ->create();

////-----------------------------------------------------------------
// add test targets
$targets[] = (new target())
    ->setName('initialTarget')
    ->setImage($img)
    ->setActive(true)
    ->setContent("<b>Hallo\nWelt!</b>")
    ->setMap('initialMap')
    ->setXPos(75)
    ->setYPos(40)
    ->setOwner($root->getUsername())
    ->create();
////
$targets[] = (new target())
    ->setName('BBT/t1')
    ->setImage($img2)
    ->setActive(true)
    ->setContent('Hi there!')
    ->setMap('initialMap')
    ->setXPos(150)
    ->setYPos(80)
    ->setOwner($root->getUsername())
    ->create();

$user = user::fromDB('BBT/Editor');
// Assign Targets
(new editor())->setTarget($targets[0])->setUser($user)->create();
//*/
// PHP SET UP -----------------------------------------------
// BEGIN RESOURCE SETUP

/* STRETCHGOAL: PERFORMANCE
// minify
$js = ""; //  todo get from udvide.js.php
minifyJS([$js, 'udvide.min.js']);
$css = ""; //  todo collect wherever it is (stretchgoal)
minifyCSS([$css, 'udvide.min.css']);*/



echo "DELETE setup.php BEFORE GOING LIVE! \n and change root password!";
