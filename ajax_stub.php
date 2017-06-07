<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.05.2017
 * Time: 09:17
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $cleanData = purifyUserDataSTUB();
        $response = null;
        switch ($cleanData['subject']) {
            case 'target':
                $target = json_decode($cleanData['target']);
                switch ($cleanData['verb']) {
                    case 'create':
                        $response = new handlerResponseSTUB();
                        if (!empty($cleanData['username']) || !empty($cleanData['passHash'])) {
                            $response->success = true;
                            $response->t_id = 2;
                        } else {
                            $response->success = false;
                            $response->message = 'Invalid Login: Can\'t create target :: Stub Error';
                        }
                        break;
                    case 'update':
                        $response = new handlerResponseSTUB();
                        if (!empty($cleanData['username']) || !empty($cleanData['passHash'])) {
                            $response->success = true;
                        } else {
                            $response->success = false;
                            $response->message = 'Invalid Login: Can\'t update Target :: Stub Error';
                        }
                        break;
                    case 'deactivate':
                        $response = new handlerResponseSTUB();
                        if (!empty($cleanData['username']) || !empty($cleanData['passHash'])) {
                            $response->success = true;
                        } else {
                            $response->success = false;
                            $response->message = 'Invalid Login: Can\'t deactivate Target :: Stub Error';
                        }
                        break;
                    case 'delete':
                        $response = new handlerResponseSTUB();
                        if (!empty($cleanData['username']) || !empty($cleanData['passHash'])) {
                            $response->success = true;
                        } else {
                            $response->success = false;
                            $response->message = 'Invalid Login: Can\'t delete Target :: Stub Error';
                        }
                        break;
                    case 'list':
                        $response = [];
                        if (!empty($cleanData['username']) || !empty($cleanData['passHash'])) {
                            $response[] = ['t_id' => 2,
                                't_owner' => 'dev/simon','xPos' => 80,'yPos' => 150,'map' => 'STUBMap',
                                'content'=> 'The shit you can do anything with','t_name' => 'STUB Target',
                                'active' => true ,'database' => 'cloudRecognition','track_rating' => 4,
                                'upl_date' => 'format?6.6.17','recos_total' => 1,
                                'recos_this_month'=>1,'recos_last_month' => 0];
                            $response[] = ['t_id' => 3,
                                't_owner' => 'dev/elias','xPos' => 70,'yPos' => 80,'map' => 'STUBMap',
                                'content'=> 'The shit you can do anything with... really','t_name' => 'STUB Target 2',
                                'active' => true ,'database' => 'cloudRecognition','track_rating' => 5,
                                'upl_date' => 'format?5.6.17','recos_total' => 10,
                                'recos_this_month'=>4,'recos_last_month' => 3];
                        } else {
                            $response = '<a href="manage.php?action=login">Login to view your targets</a>';
                        }
                        break;
                }
                return json_encode($response);
                break;
            case 'user':
                $hr = new handlerResponseSTUB();
                switch ($cleanData['verb']) {
                    case 'create':
                        if (!empty($cleanData['username']) || !empty($cleanData['passHash'])) {
                            if (!empty($cleanData['subjectName'])) {
                                $hr->success = true;
                            } else {
                                $hr->success = false;
                                $hr->message = 'subjectName required :: Stub Error';
                            }
                        } else {
                            $hr->success = false;
                            $hr->message = 'Invalid Login: Can\'t create User :: Stub Error';
                        }
                        break;
                    /*case 'update':
                        $response = $udvide->updateUser($cleanData['subjectName'],$cleanData['subjectPassword'],
                            isset($cleanData['role']) ? $cleanData['role'] : null,
                            $cleanData['username'],$cleanData['passHash']);
                        break;*/
                    case 'deactivate':
                        if (!empty($cleanData['username']) || !empty($cleanData['passHash'])) {
                            if (!empty($cleanData['subjectName'])) {
                                $hr->success = true;
                            } else {
                                $hr->success = false;
                                $hr->message = 'subjectName required :: Stub Error';
                            }
                        } else {
                            $hr->success = false;
                            $hr->message = 'Invalid Login: Can\'t deactivate User :: Stub Error';
                        }
                        break;
                    case 'delete':
                        if (!empty($cleanData['username']) || !empty($cleanData['passHash'])) {
                            if (!empty($cleanData['subjectName'])) {
                                $hr->success = true;
                            } else {
                                $hr->success = false;
                                $hr->message = 'subjectName required :: Stub Error';
                            }
                        } else {
                            $hr->success = false;
                            $hr->message = 'Invalid Login: Can\'t delete User :: Stub Error';
                        }
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
            echo $key . ': ' . $value . "\n";
        }
    }

} else {
    echo "This site is used to evaluate Ajax requests.\n
                Please go to <a href='manage.php'>the main site </a> or consult the Documentation for more information";
}

function purifyUserDataSTUB():array {
    // if every input gets the same treatment, operations on the server side
    // should always give the same result for identical client input
    $result = [];
    foreach ($_POST as $item => $value) {
        $result[$item] = htmlspecialchars(stripslashes(trim($_POST[$item])));
    }
    return $result;
}

/**
 * Represents a target with all it's keys
 * Class target
 */
class targetSTUB {
    /** @var  string */
    public $name;
    /** @var  bool */
    public $deleted;
    /** @var  string|resource */
    public $image;
    /** @var  bool */
    public $active;
    /** @var  string */
    public $content;
    /** @var  string */
    public $owner;
    /** @var  int */
    public $xPos;
    /** @var  int */
    public $yPos;
    /** @var  string */
    public $map;
    /** @var  int */
    public $id;
    /** @var  string */
    public $vw_id;
}


class handlerResponseSTUB {
    /** @var  bool */
    public $success;
    /** @var  string */
    public $message;
    /** @var  int */
    public $t_id;
}