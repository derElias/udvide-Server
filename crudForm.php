<?php
require_once 'vuforiaaccess.php';
require_once 'dbaccess/dbaUdv.php';

$sql = 'SELECT passHash, salt, role FROM udvide.Users WHERE username = ?';
$result = dba::prepareExecuteGetStatement($sql, $_POST['username']); // Documentation: this is how to follow Don't trust the user with dbaccess

$loginOk = $result !== false
    && $result['passHash'] === password_hash($result['salt'] . $_POST['passHash'],0/*ToDo*/);
if ($loginOk) { //login valid
    if (true) { //login has req permissions

    }
    $vfa = (new vuforiaaccess())
        ->setAccessmethod($_POST['access']);
    $vfa->setTargetName($_POST['name']);
    $vfa->setWidth($_POST['width']);
    $vfa->setImage($_POST['image']);
}