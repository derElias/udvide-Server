<?php
require_once 'vuforia-api/php/PostNewTarget.php';
require_once 'vuforia-api/php/UpdateTarget.php';
require_once 'vuforia-api/php/GetAllTargets.php';
require_once 'vuforia-api/php/GetTarget.php';
require_once 'vuforia-api/php/DeleteTarget.php';

class vuforiaaccess {
    private $accessmethod;
    private $target;

    /**
    * @param mixed $accessmethod
    */
    public function setAccessmethod($accessmethod)
    {
        $this->accessmethod = $accessmethod;
    }

    /**
    * @param mixed $target
    */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function execute() {
        return (new PostNewTarget())->PostNewTarget();
    }
}