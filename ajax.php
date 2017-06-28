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
        echo performVerbForSubjectAs(GET_INSTEAD_POST ? $_GET : $_POST);
    } catch (Exception $e) {
        $response = new handlerResponse();
        $response->success = false;

        $echo = '';
        $exceptionInfo = [
            'trace'=>$e->getTraceAsString(),
            'msg'=>$e->getMessage(),
            'file'=>$e->getFile(),
            'line'=>$e->getLine(),
            'code'=>$e->getCode()
        ];
        foreach ($exceptionInfo as $key=>$value) {
            $echo = $key . ': ' . $value . '<br/>';
        }
        $response->payLoad = $echo;
        echo json_encode($response);
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
function performVerbForSubjectAs(array $userInput) {
    $verb = $userInput['verb'];
    $subject = isset($userInput['subject']) ? $userInput['subject'] : null;

    $response = new handlerResponse();
    $response->success = true;

    if (!empty($userInput['user'])) {
        $user = user::fromJSON($userInput['user']);
        $subject = isset($userInput['subject']) ? $userInput['subject'] : $userInput['username'];
        // if selfedit log in the $user instance
        if ($subject === $userInput['username']) {
            $newUsername = $user->getUsername();
            $user->setUsername($subject)
                ->setPassHash($userInput['passHash'])
                ->login()
                ->setUsername($newUsername);
        } else { // todo refactor this switch case
            loginUser($userInput['username'], $userInput['passHash']);
        }
        $response->payLoad = performVerbForUser($verb, $user, $subject);
    } elseif (!empty($userInput['target'])) {
        loginUser($userInput['username'], $userInput['passHash']);
        $target = target::fromJSON($userInput['target']);
        $response->payLoad = performVerbForTarget($verb, $target, $subject);
    } elseif (!empty($userInput['map'])) {
        loginUser($userInput['username'], $userInput['passHash']);
        $map = map::fromJSON($userInput['map']);
        $response->payLoad = performVerbForMap($verb, $map, $subject);
    } elseif ($verb == 'readAll') {
        loginUser($userInput['username'], $userInput['passHash']);
        $response->payLoad = getSwitch($userInput);
    } else {
        $response->success = false;
        $response->payLoad = ERR_UD010;
    }
    return json_encode($response);
}

function getSwitch($userInput) {
    switch ($userInput['subject']) {
        case 'target':
            return target::readAll();
            break;
        case 'user':
            return user::readAll();
            break;
        case 'map':
            return map::readAll();
            break;
        case 'initial':

            break;
    }
}

function loginUser(string $username, string $passHash) {
    user::fromDB($username)
        ->setPassHash($passHash)
        ->login();
}

function performVerbForTarget(string $verb,target $target, string $subject) {
    switch ($verb) {
        case 'create':
            $target->create();
            return true;
        case 'read':
            return $target->read();
        case 'update':
            $target->update($subject);
            return true;
        case 'delete':
            $target->delete();
            return true;
        default:
            throw new InvalidVerbException();
    }
}

function performVerbForUser(string $verb,user $user,string $subject) {
    switch ($verb) {
        case 'create':
            $user->create();
            return true;
        case 'read':
            return $user->read();
        case 'update':
            $user->update($subject);
            return true;
        case 'delete':
            $user->delete();
            return true;
        default:
            throw new InvalidVerbException();
    }
}

function performVerbForMap(string $verb,map $map,string $subject) {
    switch ($verb) {
        case 'create':
            $map->create();
            return true;
        case 'read':
            return $map->read();
        case 'update':
            //$map->update($subject);
            return true;
        case 'delete':
            $map->delete();
            return true;
        default:
            throw new InvalidVerbException();
    }
}