<?php
require_once 'udvide.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.05.2017
 * Time: 09:17
 */
// If valid Post and Production or Get and Testing
if ($_SERVER["REQUEST_METHOD"] == "POST" && !GET_INSTEAD_POST
    || $_SERVER["REQUEST_METHOD"] == "GET" && GET_INSTEAD_POST) {
    try {
        // try to perform the requested action
        echo performVerbForSubject();
    } catch (Exception $e) {
        $exceptionInfo = [
            'trace'=>$e->getTraceAsString(),
            'msg'=>$e->getMessage(),
            'file'=>$e->getFile(),
            'line'=>$e->getLine(),
            'code'=>$e->getCode()
        ];
        foreach ($exceptionInfo as $key=>$value) {
            echo $key . ': ' . $value;
        }
    }
} else {
    echo "This site is used to evaluate Ajax requests.\n
                Please go to <a href='manage.php'>the main site </a> or consult the Documentation for more information";
}

/**
 * Tries to answer the get or post request
 * @return string
 */
function performVerbForSubject() {
    $cleanData = purifyUserData();
    $udvide = new udvide();
    $response = null;
    switch ($cleanData['subject']) {
        case 'target':
            var_dump($cleanData['target']);
            $target = target::fromJSON($cleanData['target']);
            switch ($cleanData['verb']) {
                case 'create':
                    $response = $udvide->doTargetManipulationAs('create', $target, $cleanData['username'], $cleanData['passHash']);
                    break;
                case 'read':
                    //$response = $udvide->readTarget($target);
                    break;
                case 'update':
                    $response = $udvide->doTargetManipulationAs('update', $target, $cleanData['username'], $cleanData['passHash']);
                    break;
                case 'deactivate':
                    $response = $udvide->doTargetManipulationAs('deactivate', $target, $cleanData['username'], $cleanData['passHash']);
                    break;
                case 'delete':
                    $response = $udvide->doTargetManipulationAs('delete', $target, $cleanData['username'], $cleanData['passHash']);
                    break;
                case 'list':
                    $response = $udvide->getTargetPageByUser($cleanData['username'], $cleanData['passHash'],
                        isset($cleanData['page']) ? $cleanData['page'] : 0,
                        isset($cleanData['pageSize']) ? $cleanData['pageSize'] : 25);
                    break;
            }
            return json_encode($response);
            break;
        case 'user':
            $hr = new handlerResponse();
            switch ($cleanData['verb']) {
                case 'create':
                    $response = $udvide->createUser($cleanData['subjectName'], $cleanData['subjectPassword'],
                        isset($cleanData['role']) ? $cleanData['role'] : null,
                        $cleanData['username'], $cleanData['passHash']);
                    $hr->success = $response;
                    break;
                /*case 'update':
                    $response = $udvide->updateUser($cleanData['subjectName'],$cleanData['subjectPassword'],
                        isset($cleanData['role']) ? $cleanData['role'] : null,
                        $cleanData['username'],$cleanData['passHash']);
                    break;*/
                case 'deactivate':
                    $response = $udvide->deactivateUser($cleanData['subjectName'],
                        $cleanData['username'], $cleanData['passHash']);
                    $hr->success = $response;
                    break;
                case 'delete':
                    $response = $udvide->$response = $udvide->deleteUser($cleanData['subjectName'],
                        $cleanData['username'], $cleanData['passHash']);
                    $hr->success = $response;
                    break;
                /*case 'list':
                    $response = $udvide->getTargetPageByUser($cleanData['username'], $cleanData['passHash'],
                        isset($cleanData['page']) ? $cleanData['page'] : null,
                        isset($cleanData['pageSize']) ? $cleanData['pageSize'] : null);
                    $hr = $response;
                    break;*/
            }
            return json_encode($hr);
            break;
        default:
            $hr = new handlerResponse();
            $hr->success = false;
            $hr->message = ERR_UD010;
            return json_encode($hr);
            break;
    }
}

function purifyUserData():array {
    // if every input gets the same treatment, operations on the server side
    // should always give the same result for identical client input
    $in = GET_INSTEAD_POST ? $_GET : $_POST;
    $result = [];
    foreach ($in as $item => $value) {
        $result[$item] = htmlspecialchars(stripslashes(trim($in[$item])));
    }
    return DIRECT_USERDATA ? $in : $result;
}
