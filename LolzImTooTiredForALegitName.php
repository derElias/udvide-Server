<?php
require_once 'vuforiaaccess.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.05.2017
 * Time: 10:34
 */
class lolzImTooTiredForALegitName
{
    public function submitNewTarget()
    {

    }
}
// ToDo If Logged in
$vfa = (new vuforiaaccess())
    ->setAccessmethod($_POST['access']);
$vfa->setTargetName($_POST['name']);
$vfa->setWidth($_POST['width']);
$vfa->set($_POST['width']);