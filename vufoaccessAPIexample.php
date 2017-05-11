<?php
include_once 'vuforiaaccess.php';
include_once 'vfcAccess.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.05.2017
 * Time: 01:28
 */


$tName = 'vfcAccess Test #1';
$VuFoAccess = new vfcAccess();
// echo file_get_contents('img/First.png');
$response = $VuFoAccess
    ->setAccessmethod('GET')
    ->setTargetName($tName)
    ->setMeta("/clientRequest.php#h=[client]" . $tName)
    ->setImageByPath('img/img.jpg')
    ->setActiveflag(true)
    ->setTargetId('c7a6d67295a146f1b82404c71801f230')
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
