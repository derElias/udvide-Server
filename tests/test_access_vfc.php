<?php
include_once '../access_vfc.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.05.2017
 * Time: 01:28
 */

function test()
{
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
    try {
        $VuFoAccess = new vfcAccess();
        printResponse($VuFoAccess
            ->execute('BULLSHIT'));
    } catch (Exception $e) {
        echo 'Expected Exception: ' . $e->getMessage();
    }

    try {
        $VuFoAccess = new vfcAccess();
        printResponse($VuFoAccess
            ->setImage('Dis is no image but a string')
            ->execute('post'));
    } catch (Exception $e) {
        echo 'Expected Exception: ' . $e->getMessage();
    }

    try {
        $VuFoAccess = new vfcAccess();
        printResponse($VuFoAccess
            ->setTargetName(null)
            ->execute('post'));
    } catch (Exception $e) {
        echo 'Expected Exception: ' . $e->getMessage();
    }
}

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