<?php
require_once 'vuforia-api/php/PostNewTarget.php';
require_once 'vuforia-api/php/UpdateTarget.php';
require_once 'vuforia-api/php/GetAllTargets.php';
require_once 'vuforia-api/php/GetTarget.php';
require_once 'vuforia-api/php/DeleteTarget.php';

class vuforiaaccess {
    private $accessmethod;

    private $targetId 	    = false;
    private $targetName 	= false;
    private $image        	= false;
    private $width			= false;
    private $meta			= false;
    private $activeflag		= false;

    public function execute() {
        switch ($this->accessmethod) {
            case POST:
                return (new PostNewTarget())
                    ->setName($this->targetName)
                    ->setImage($this->image)
                    ->setWidth($this->width)
                    ->setMeta($this->meta)
                    ->setActiveflag($this->activeflag)
                    ->PostNewTarget();
                break;
            case GET:
                break;
            case GETALL:
                break;
            case UPDATE:
                break;
            case DELETE:
                break;
            default:
                trigger_error("INVALID VUFORIAACCESS OPERATION!\n
                Got $this->accessmethod instead of POST, GET, GETALL, UPDATE or DELETE!",E_USER_ERROR);
                break;
        }
        return (new PostNewTarget())->PostNewTarget();
    }

    /**
     * Fluent Setter and Getter vor EVERYTHING
     */

    /**
     * @param mixed $accessmethod
     * @return vuforiaaccess
     */
    public function setAccessmethod($accessmethod)
    {
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
     * @param string $image
     * @return vuforiaaccess
     */
    public function setImage(string $image): vuforiaaccess
    {
        $this->image = $image;
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
     * @param int $activeflag
     * @return vuforiaaccess
     */
    public function setActiveflag(int $activeflag): vuforiaaccess
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
     * @return mixed
     */
    public function getAccessmethod()
    {
        return $this->accessmethod;
    }
}