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
    $VuFoAccess = new access_vfc();
    printResponse($VuFoAccess
        ->setAccessMethod('sumAll')
        ->execute());

// Get All
    $VuFoAccess = new access_vfc();
    printResponse($VuFoAccess
        ->setAccessMethod('getAll')
        ->execute());

// Post
    $tName = 'access_vfc() Test #2 POST';
    $VuFoAccess = new access_vfc();
    printResponse($VuFoAccess
        ->setTargetName($tName)
        ->setMeta("/clientRequest.php#h=[client]" . $tName)
        ->setImageByPath('img/img.jpg')
        ->setActiveflag(true)
        ->setAccessMethod('create')
        ->execute());


    $testTargetID = 'e3716692633e4ce69ac8d160b038180b'; // der marker von der ersten demo

// Get
    $VuFoAccess = new access_vfc();
    printResponse($VuFoAccess
        ->setTargetId($testTargetID)
        ->setAccessMethod('get')
        ->execute());

// Update
    $tName = 'access_vfc() Test #2 UPDATED';
    $VuFoAccess = new access_vfc();
    printResponse($VuFoAccess
        ->setTargetId($testTargetID)
        ->setTargetName($tName)
        ->setMeta("/clientRequest.php#h=[client]" . $tName)
        ->setAccessMethod('update')
        ->execute());

// Summarize
    $VuFoAccess = new access_vfc();
    printResponse($VuFoAccess
        ->setTargetId($testTargetID)
        ->setAccessMethod('summarize')
        ->execute());

    /* should work if get works ;)
    $VuFoAccess = new access_vfc();
    printResponse($VuFoAccess
        ->setTargetId($testTargetID)
        ->setAccessMethod('delete')
        ->execute()); */


// these should fail:
    try {
        $VuFoAccess = new access_vfc();
        printResponse($VuFoAccess
            ->setAccessMethod('BULLSHIT')
            ->execute());
    } catch (Exception $e) {
        echo 'Expected Exception: ' . $e->getMessage();
    }

    try {
        $VuFoAccess = new access_vfc();
        printResponse($VuFoAccess
            ->setImage('Dis is no image but a string')
            ->setAccessMethod('post')
            ->execute());
    } catch (Exception $e) {
        echo 'Expected Exception: ' . $e->getMessage();
    }

    try {
        $VuFoAccess = new access_vfc();
        printResponse($VuFoAccess
            ->setTargetName(null)
            ->setAccessMethod('post')
            ->execute());
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