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
        echo performVerbForSubject(GET_INSTEAD_POST ? $_GET : $_POST);
    } catch (Exception $e) {
        $exceptionInfo = [
            'trace'=>$e->getTraceAsString(),
            'msg'=>$e->getMessage(),
            'file'=>$e->getFile(),
            'line'=>$e->getLine(),
            'code'=>$e->getCode()
        ];
        foreach ($exceptionInfo as $key=>$value) {
            echo $key . ': ' . $value . '<br/>';
        }
    }
} else {
    echo "This site is used to evaluate Ajax requests.\n<br/>
                Please go to <a href='manage.php'>the main site </a> or consult the Documentation for more information";
}

/**
 * Tries to answer the get or post request
 * @param array $userInput
 * @return string
 */
function performVerbForSubject(array $userInput) {
    $udvide = new udvide();
    $response = null;
    switch ($userInput['subject']) {
        case 'target':
            $target = target::fromJSON($userInput['target']);
            switch ($userInput['verb']) {
                case 'create':
                    $response = $udvide->doTargetManipulationAs('create', $target, $userInput['username'], $userInput['passHash']);
                    break;
                case 'read':
                    //$response = $udvide->readTarget($target);
                    break;
                case 'update':
                    $response = $udvide->doTargetManipulationAs('update', $target, $userInput['username'], $userInput['passHash']);
                    break;
                case 'deactivate':
                    $response = $udvide->doTargetManipulationAs('deactivate', $target, $userInput['username'], $userInput['passHash']);
                    break;
                case 'delete':
                    $response = $udvide->doTargetManipulationAs('delete', $target, $userInput['username'], $userInput['passHash']);
                    break;
                case 'list':
                    $response = $udvide->getTargetPageByUser($userInput['username'], $userInput['passHash'],
                        isset($userInput['page']) ? $userInput['page'] : 0,
                        isset($userInput['pageSize']) ? $userInput['pageSize'] : 25);
                    break;
            }
            return json_encode($response);
            break;
        case 'user':
            $hr = new handlerResponse();
            switch ($userInput['verb']) {
                case 'create':
                    $response = $udvide->createUser($userInput['subjectName'], $userInput['subjectPassword'],
                        isset($userInput['role']) ? $userInput['role'] : null,
                        $userInput['username'], $userInput['passHash']);
                    $hr->success = $response;
                    break;
                /*case 'update':
                    $response = $udvide->updateUser($userInput['subjectName'],$userInput['subjectPassword'],
                        isset($userInput['role']) ? $userInput['role'] : null,
                        $userInput['username'],$userInput['passHash']);
                    break;*/
                case 'deactivate':
                    $response = $udvide->deactivateUser($userInput['subjectName'],
                        $userInput['username'], $userInput['passHash']);
                    $hr->success = $response;
                    break;
                case 'delete':
                    $response = $udvide->$response = $udvide->deleteUser($userInput['subjectName'],
                        $userInput['username'], $userInput['passHash']);
                    $hr->success = $response;
                    break;
                /*case 'list':
                    $response = $udvide->getTargetPageByUser($userInput['username'], $userInput['passHash'],
                        isset($userInput['page']) ? $userInput['page'] : null,
                        isset($userInput['pageSize']) ? $userInput['pageSize'] : null);
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
