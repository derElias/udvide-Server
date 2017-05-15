<?php
include_once 'vfcAccess.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.05.2017
 * Time: 01:28
 *
 * right now this is more of a test than an actual reference
 */


// Summarize all
$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->execute('suMaLl'));

// Get All
$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->execute('GetAll'));

// Post
$tName = 'vfcAccess Test #2 POST';
$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->setTargetName($tName)
    ->setMeta("/clientRequest.php#h=[client]" . $tName)
    ->setImageByPath('img/img.jpg')
    ->setActiveflag(true)
    ->execute('poSt'));


$testTargetID = 'e3716692633e4ce69ac8d160b038180b'; // der marker von der ersten demo

// Get
$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->setTargetId($testTargetID)
    ->execute('GET'));

// Update
$tName = 'vfcAccess Test #2 UPDATED';
$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->setTargetId($testTargetID)
    ->setTargetName($tName)
    ->setMeta("/clientRequest.php#h=[client]" . $tName)
    ->execute('updAte'));

// Summarize
$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->setTargetId($testTargetID)
    ->execute('s'));

/* should work if get works ;)
$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->setTargetId($testTargetID)
    ->execute('del')); */


// these should fail:
$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->execute('BULLSHIT'));

$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->setImage('Dis is no image but a string')
    ->execute('post'));

$VuFoAccess = new vfcAccess();
printResponse($VuFoAccess
    ->setTargetName(null)
    ->execute('post'));


function printResponse(HTTP_Request2_Response $response)
{
    $nonError = [200, 201];
    if (in_array($response->getStatus(), $nonError)) {
        echo $response->getBody();
    } else {
        switch ($response->getStatus()) {
            case '404':
                echo '<div class="errorPopup">404: Target not found</div>';
                break;
            default:
                echo '<div class="errorPopup">' . var_dump($response) . '</div>';
        }
    }
    echo '<br/><br/>';
}