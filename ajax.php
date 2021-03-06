<?php
require_once 'vendor/autoload.php';
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
        $result = performVerbForSubjectAs(GET_INSTEAD_POST ? $_GET : $_POST);
        header('Content-Type: application/json');
        echo $result;
    } catch (Exception $e) {
        $response = new handlerResponse();
        $response->success = false;

        $echo = '';
        if (!THIS_IS_PRODUCTIOOOON) {
            $echo = [
                'msg' => $e->getMessage(),
                'trace' => explode("\n",$e->getTraceAsString()),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode()
            ];
        } else {
            $echo = $e->getMessage();
        }
        $response->payLoad = $echo;
        header('Content-Type: application/json');
        echo json_encode($response);
    }
} else {
    echo "This site is used to evaluate Ajax requests.\n<br/>
                Please go to <a href='manage.html'>the main site </a> or consult the Documentation for more information";
}

/**
 * Tries to answer the get or post request
 * @param array $userInput
 * @return string
 */
function performVerbForSubjectAs(array $userInput) {
    $verb = $userInput['verb'];

    $response = new handlerResponse();
    $response->success = true;

    if (!empty($userInput['user'])) {
        $sentUser = user::fromJSON($userInput['user']);
        $userInput['updateSubject'] = isset($userInput['updateSubject']) ? $userInput['updateSubject'] : $sentUser->getUsername();

        // if selfedit log in the $user instance
        if ($userInput['updateSubject'] === $userInput['username']) {
            $user = user::fromDB($userInput['username'])
                ->setPassHash($userInput['passHash'])
                ->login()
                ->setUsername($sentUser->getUsername())
                ->setPassHash($sentUser->getPassHash());
        } else {
            loginUser($userInput['username'], $userInput['passHash']);
            $user = $sentUser;
        }
        $response->payLoad = performVerbForUser($verb, $user, $userInput['updateSubject']);

    } elseif (!empty($userInput['target'])) {
        loginUser($userInput['username'], $userInput['passHash']);
        $target = target::fromJSON($userInput['target']);
        $userInput['updateSubject'] = isset($userInput['updateSubject']) ? $userInput['updateSubject'] : $target->getName();
        $response->payLoad = performVerbForTarget($verb, $target, $userInput['updateSubject']);

    } elseif (!empty($userInput['map'])) {
        loginUser($userInput['username'], $userInput['passHash']);
        $map = map::fromJSON($userInput['map']);
        $userInput['updateSubject'] = isset($userInput['updateSubject']) ? $userInput['updateSubject'] : $map->getName();
        $response->payLoad = performVerbForMap($verb, $map, $userInput['updateSubject']);

    } elseif ($verb == 'readAll') {
        loginUser($userInput['username'], $userInput['passHash']);
        $response->payLoad = getSwitch($userInput);
    } elseif ($verb == 'clean') {
        // for restoration and not deleting database connection and for error suppression we mark all "deleted" entries as such.
        // clean allows admins full deletion
        loginUser($userInput['username'], $userInput['passHash']);
        if (user::getLoggedInUser()->getRole() > MIN_ALLOW_CLEAN)
            helper::cleanupDbAndVfc();
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
        case 'target+edit':
            $targets = target::readAll();
            foreach ($targets as $key=>$target) {
                $targets[$key]['editors'] = editor::readAllUsersFor($target['name']);
            }
            return $targets;
            break;
        case 'user+edit':
            $users = user::readAll();
            foreach ($users as $key=>$user) {
                $users[$key]['editors'] = editor::readAllTargetsFor($user['username']);
            }
            return $users;
            break;
        case 'map':
            return map::readAll();
            break;
        case 'editors':
            return editor::readAll();
            break;
        case 'initial':
            $targets = target::readAll();
            $users = user::readAll();
            $maps = map::readAll();
            foreach ($targets as $key=>$target) {
                $targets[$key]['editors'] = editor::readAllUsersFor($target['name']);
            }
            foreach ($users as $key=>$user) {
                $users[$key]['editors'] = editor::readAllTargetsFor($user['username']);
            }
            return ['targets' => $targets,
                'users' => $users,
                'maps' => $maps];
            break;
        default:
            throw new InvalidVerbException('Invalid Subject for readAll');
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
            $map->update($subject);
            return true;
        case 'delete':
            $map->delete();
            return true;
        default:
            throw new InvalidVerbException();
    }
}