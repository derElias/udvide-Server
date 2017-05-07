<?php
require_once 'c:/xampp/php/pear/HTTP/Request2.php'; // change this line to the HTTP/Request2.php path

require_once 'vuforia-api/php/SignatureBuilder.php';

require_once 'vuforia-api/php/PostNewTarget.php';
require_once 'vuforia-api/php/UpdateTarget.php';
require_once 'vuforia-api/php/GetAllTargets.php';
require_once 'vuforia-api/php/GetTarget.php';
require_once 'vuforia-api/php/DeleteTarget.php';

class vuforiaaccess {
    private static $url = "https://vws.vuforia.com";
    private static $requestPath = "/targets";
    private static $secretKey = '...';
    private static $accessKey = '...';

    private $accessmethod;
    private $jsonresponse;

    private $targetId;
    private $targetName;
    private $image;
    private $width;
    private $meta;
    private $activeflag;

    /**
     * @return string
     */
    public static function getSecretKey()
    {
        return self::$secretKey;
    }

    /**
     * @return string
     */
    public static function getAccessKey()
    {
        return self::$accessKey;
    }

    /**
     * @return string
     */
    public static function getRequestPath(): string
    {
        return self::$requestPath;
    }

    /**
     * @return string
     */
    public static function getUrl(): string
    {
        return self::$url;
    }

    /**
     * @return mixed
     */
    public function execute() {
        switch ($this->accessmethod) {
            case 'POST':
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
                $this->jsonresponse = $subject
                    ->validateData()
                    ->execute();
                break;
            case 'GET':
                $this->jsonresponse = (new GetTarget())
                    ->setTargetId($this->targetId)
                    ->validateData()
                    ->execute();
                break;
            case 'GETALL':
                $this->jsonresponse = (new GetAllTargets())
                    ->validateData()
                    ->execute();
                break;
            case 'UPDATE':
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
                $this->jsonresponse = $subject
                    ->validateData()
                    ->execute();
                break;
            case 'DELETE':
                $this->jsonresponse = (new DeleteTarget())
                    ->setTargetId($this->targetId)
                    ->validateData()
                    ->execute();
                break;
            default:
                trigger_error("INVALID VUFORIAACCESS OPERATION!\n
                Got $this->accessmethod instead of POST, GET, GETALL, UPDATE or DELETE!",E_USER_ERROR);
                break;
        }
        // VuFo Response is now stored in $jsonresponse
        return json_decode($this->jsonresponse); // ToDo
    }

    /**
     * Fluent Setter and Getter for EVERYTHING
     */

    /**
     * @param string $accessmethod
     * @return vuforiaaccess
     */
    public function setAccessmethod($accessmethod)
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
}

interface VuFoWorker {
    public function __construct();
    public function execute();
    public function validateData();
}