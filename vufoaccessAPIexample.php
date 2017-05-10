<?php
include_once 'vuforiaaccess.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.05.2017
 * Time: 01:28
 */
$tName = 'peterbraun';
$VuFoAccess = new vuforiaaccess();
// echo file_get_contents('img/First.png');
$response = $VuFoAccess
    ->setAccessmethod('POST')
    ->setTargetName($tName)
    ->setWidth('320')
    ->setMeta("http://" . gethostname() . "/clientRequest.php#h=[client]" . $tName)
    ->setImageByPath('img/img.jpg')
    ->setActiveflag(true)
    ->execute();/* */

$nonError = [200,201];
if (in_array($response->getStatus(), $nonError)) {
    echo $response->getBody();
} else {
    switch($response->getStatus()) {
        case '404':
            echo '<div class="errorPopup">404: Target not found</div>';
            break;
        default:
            echo '<div class="errorPopup">' . var_dump($response) . '</div>';
    }
}
