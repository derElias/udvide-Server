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
    ->setWidth('910')
    ->setMeta("http://" . gethostname() . "/clientrequest.php#h=[kundennummer][lokale ID]")
    ->setImageByPath('img/logo.png')
    ->execute();/* */

var_dump($response);

$nonError = [200,201];
if (in_array($response->getStatus(), $nonError)) {
    echo $response->getBody();
} else {
    switch($response->getStatus()) {
        case '404':
            echo '<div class="errorPopup">404: Target not found</div>';
            break; // ToDo
    }
}
