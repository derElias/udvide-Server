<?php
include_once 'vuforiaaccess.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.05.2017
 * Time: 01:28
 */
$VuFoAccess = new vuforiaaccess();
$response = $VuFoAccess
    ->setAccessmethod('POST')
    ->setTargetName('Example')
    ->setWidth('320')
    ->setMeta("http://" . gethostname() . "/clientrequest.php#h=[kundennummer][lokale ID]")
    ->setImageByPath('/img/logo.png')
    ->execute();

$nonError = [200,201];
if (in_array($response->getStatus(), $nonError)) {
    echo $response->getBody();
} else {
    $VuFoAccess->handleError($response);
}