<?php
require_once 'udvide.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.05.2017
 * Time: 09:17
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") { // if form submit
    echo '<!DOCTYPE html><html><head></head><body>';
    try {
        $handlerResponse = handleForm();
        echo '<p>In future versions the software will handle the form async and not replace the page</p></br>';
        if ($handlerResponse->success) {
            echo '<p>';
            echo $handlerResponse->payLoad;
            echo '</br>';
            echo $handlerResponse->t_id;
            echo '</p>';
        } else {
            echo '<div class="popMessage"><p>';
            echo $handlerResponse->payLoad;
            echo '</p></div>';
        }
    } catch (Exception $e) {
        $exceptionInfo = [
            'trace'=>$e->getTraceAsString(),
            'msg'=>$e->getMessage(),
            'file'=>$e->getFile(),
            'line'=>$e->getLine(),
            'code'=>$e->getCode()
        ];
        echo '<div class="popMessage"><p>An Error occurred! Please give the udvide team this info:<br/>';
        foreach ($exceptionInfo as $key=>$value) {
            echo $key . ': ' . $value;
        }
        echo '</p></div>';
        //echo json_encode($exceptionInfo);
    }
    echo '</body></html>';
} else {
    echo "This site is used to evaluate CRUD Form requests.\n
                Please use the form or consult the Documentation for more information";
}

/**
 * @return handlerResponse
 */
function handleForm()
{
    $cleanData = purifyUserData();
    $target = arrayToTarget($cleanData);
    $verb = $cleanData['udvideVerb'];
    $user = $cleanData['username'];
    $passH = $cleanData['passHash'];
    return (new udvide())->doTargetManipulationAs($verb, $target, $user, $passH);
}

/**
 * @return array
 */
function purifyUserData():array {
    // if every input gets the same treatment, operations on the server side
    // should always give the same result for identical client input
    $result = [];
    foreach ($_POST as $item => $value) {
        $result[$item] = htmlspecialchars(stripslashes(trim($_POST[$item])));
    }
    return $result;
}
