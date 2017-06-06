<?php
require_once 'udvide.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.05.2017
 * Time: 09:17
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $cleanData = purifyUserData();
        $udvide = new udvide();
        $response = null;
        switch ($cleanData['subject']) {
            case 'target':
                $target = json_decode($cleanData['target']);
                switch ($cleanData['verb']) {
                    case 'create':
                        $response = $udvide->doTargetManipulationAs('create', $target, $cleanData['username'], $cleanData['passHash']);
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
                            isset($cleanData['page']) ? $cleanData['page'] : null,
                            isset($cleanData['pageSize']) ? $cleanData['pageSize'] : null);
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
        }
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

function purifyUserData():array {
    // if every input gets the same treatment, operations on the server side
    // should always give the same result for identical client input
    $result = [];
    foreach ($_POST as $item => $value) {
        $result[$item] = htmlspecialchars(stripslashes(trim($_POST[$item])));
    }
    return $result;
}
