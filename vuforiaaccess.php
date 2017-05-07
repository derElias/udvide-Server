<?php
require_once 'c:/xampp/php/pear/HTTP/Request2.php'; // change this line to the HTTP/Request2.php path

require_once 'vuforia-api/php/SignatureBuilder.php';

require_once 'vuforia-api/php/PostNewTarget.php';
require_once 'vuforia-api/php/UpdateTarget.php';
require_once 'vuforia-api/php/GetAllTargets.php';
require_once 'vuforia-api/php/GetTarget.php';
require_once 'vuforia-api/php/DeleteTarget.php';

include_once 'vuforia-api/php/DefaultErrorHandle.php';

class vuforiaaccess {
    private static $url = "https://vws.vuforia.com";
    private static $targetRequestPath = "/targets";
    private static $targetSummaryPath = "/summary";
    private static $secretKey = '...'; // change this to secretKey
    private static $accessKey = '...'; // change this to Server accessKey

    private $accessmethod;

    private $targetId;
    private $targetName;
    private $image;
    private $width;
    private $meta;
    private $activeflag;

    //<editor-fold desc="static Getters">
    /**
     * @return string
     */
    public static function getSecretKey():string
    {
        return self::$secretKey;
    }

    /**
     * @return string
     */
    public static function getAccessKey():string
    {
        return self::$accessKey;
    }

    /**
     * @return string
     */
    public static function getTargetRequestPath(): string
    {
        return self::$targetRequestPath;
    }

    /**
     * @return string
     */
    public static function getUrl(): string
    {
        return self::$url;
    }

    /**
     * @return string
     */
    public static function getTargetSummaryPath(): string
    {
        return self::$targetSummaryPath;
    }
    //</editor-fold>

    /**
     * @return mixed
     */
    public function execute() {
        $this->accessmethod = strtoupper($this->accessmethod);
        switch ($this->accessmethod) {
            case 'POST':
                $response = $this->callPost();
                break;
            case 'GET':
                $response = $this->callGet();
                break;
            case 'GETALL':
                $response = $this->callGetAll();
                break;
            case 'UPD':
            case 'UPDATE':
                $response = $this->callUpdate();
                break;
            case 'DEL':
            case 'DELETE':
                $response = $this->callDelete();
                break;
            case 'SUM':
            case 'SUMMARIZE':
            case 'SUMMARY':
                $response = $this->callSummary();
                break;
            case 'SUMALL':
            case 'SUMMARIZEALL':
            case 'SUMMARYALL':
                $response = $this->callSummaryAll();
                 break;
            default:
                trigger_error("INVALID VUFORIAACCESS OPERATION!\n
                Got $this->accessmethod instead of POST, GET, GETALL, UPDATE, UPD, DELETE, DEL,\n
                SUM, SUMMARIZE, SUMMARY, SUMALL, SUMMARIZEALL, SUMMARYALL!",E_USER_ERROR);
                $response = 'trigger_error dosnt seem to work properly...';
                break;
        }
        return $response;
    }

    public function handleError($response)
    {
        switch($response->getStatus()) {
            case '404':
                echo '<div class="errorpopup">404: Target not found</div>';
                break; // ToDo
        }
    }

    //<editor-fold desc="Calls">
    private function callPost()
    {
        $subject = new PostNewTarget();
        $subject
            ->setName($this->targetName)
            ->setImage($this->image);
        if (!empty($this->width)) {
            $subject->setWidth($this->width);
        }
        if (!empty($this->meta)) {
            $subject->setMeta($this->meta);
        }
        if (!empty($this->activeflag)) {
            $subject->setActiveflag($this->activeflag);
        }
        return $subject
            ->validateData()
            ->execute();
    }

    private function callGet()
    {
        return (new GetTarget())
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callGetAll()
    {
        return (new GetAllTargets())
            ->validateData()
            ->execute();
    }

    private function callUpdate()
    {
        $subject = new UpdateTarget();
        if (!empty($this->targetName)) {
            $subject->setName($this->targetName);
        }
        if (!empty($this->image)) {
            $subject->setImage($this->image);
        }
        if (!empty($this->width)) {
            $subject->setWidth($this->width);
        }
        if (!empty($this->meta)) {
            $subject->setMeta($this->meta);
        }
        if (!empty($this->activeflag)) {
            $subject->setActiveflag($this->activeflag);
        }
        return $subject
            ->validateData()
            ->execute();
    }

    private function callDelete()
    {
        return (new DeleteTarget())
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callSummary()
    {
        return (new GetSummary())
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callSummaryAll()
    {
        return (new GetAllSummaries())
            ->validateData()
            ->execute();
    }
    //</editor-fold>

    //<editor-fold desc="Fluent Setters">

    /**
     * @param string $accessmethod
     * @return vuforiaaccess
     */
    public function setAccessmethod($accessmethod):vuforiaaccess
    {
        if ($accessmethod != 'POST' &&
            $accessmethod != 'GET' &&
            $accessmethod != 'GETALL' &&
            $accessmethod != 'UPDATE' &&
            $accessmethod != 'DELETE')
            trigger_error("$this->accessmethod is not a valid AccessMethod for VuforiaAccess.\n
            Use POST, GET, GETALL, UPDATE or DELETE instead!");

        $this->accessmethod = $accessmethod;
        return $this;
    }

    /**
     * @param string $targetId
     * @return vuforiaaccess
     */
    public function setTargetId(string $targetId): vuforiaaccess
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * @param string $targetName
     * @return vuforiaaccess
     */
    public function setTargetName(string $targetName): vuforiaaccess
    {
        $this->targetName = $targetName;
        return $this;
    }

    /**
     * @param string $image // base 64 encoded
     * @return vuforiaaccess
     */
    public function setImage(string $image): vuforiaaccess
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param string $imagePath
     * @return vuforiaaccess
     */
    public function setImageByPath(string $imagePath): vuforiaaccess
    {
        $this->image = base64_encode(file_get_contents($imagePath));
        return $this;
    }

    /**
     * @param float $width
     * @return vuforiaaccess
     */
    public function setWidth(float $width): vuforiaaccess
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param string $meta
     * @return vuforiaaccess
     */
    public function setMeta(string $meta): vuforiaaccess
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param bool $activeflag
     * @return vuforiaaccess
     */
    public function setActiveflag(bool $activeflag): vuforiaaccess
    {
        $this->activeflag = $activeflag;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Getters">
    /**
     * @return string
     */
    public function getTargetId(): string
    {
        return $this->targetId;
    }

    /**
     * @return string
     */
    public function getTargetName(): string
    {
        return $this->targetName;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @return string
     */
    public function getMeta(): string
    {
        return $this->meta;
    }

    /**
     * @return int
     */
    public function getActiveflag(): int
    {
        return $this->activeflag;
    }

    /**
     * @return string
     */
    public function getAccessmethod(): string
    {
        return $this->accessmethod;
    }
    //</editor-fold>

}

interface VuFoWorker {
    public function __construct();
    public function execute();
    public function validateData();
}
