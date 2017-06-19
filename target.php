<?php
require_once 'helper.php';
/**
 * Created by PhpStorm.
 * User: Simon, all code in this file, except explicitly stated otherwise is written by me
 * Date: 14.06.2017 Refactor in new file
 * Time: 15:48
 */
class target
{
    // SQL only Values
    /** @var int */
    private $id;
    /** @var  bool */
    private $deleted;
    /** @var  string */
    private $owner;
    /** @var  string WARNING: UNESCAPED */
    private $content;
    /** @var  int */
    private $xPos;
    /** @var  int */
    private $yPos;
    /** @var  string */
    private $map;

    // Shared Values
    /** @var string varchar(32) */
    private $vw_id;
    /** @var  resource */
    private $image;

    // VWS only Values
    /** @var  string */
    private $name;
    /** @var  bool */
    private $active;

    //<editor-fold desc="Constructors">
    /**
     * target constructor.
     */
    private function __construct() {}

    /**
     * indirect constructor
     * @param string $json
     * @return target
     */
    public static function fromJSON($json = '') {
        $instance = new self();
        if (!empty($json))
            $instance->set(json_decode($json, true));
        return $instance;
    }

    /**
     * indirect constructor
     * @param array $array
     * @return target
     */
    public static function fromArray(array $array = null) {
        $instance = new self();
        if (!empty($array))
            $instance->set($array);
        return $instance;
    }

    /**
     * indirect constructor
     * @param null $id
     * @return target
     * @internal param bool $array
     */
    public static function fromDB($id = null) {
        $instance = new self();
        if (isset($id)) {
            $instance->setId($id)->read();
        }
        return $instance;
    }
    //</editor-fold>

    public function read() {
        // ToDo from udvide
    }

    public function create() {
        // ToDo new
    }
    public function update() {
        // ToDo from udvide
    }
    public function deactivate() {
        // ToDo from udvide
    }
    public function delete() {
        // ToDo from udvide
    }

    /**
     * Fills the target from an array
     * @param array $data
     */
    public function set(array $data)
    {
        foreach ($data AS $key => $value) {
            $this->__set($key, $value); // To use setters behind permission and type verification
        }
    }

    /**
     * Set via available Fluent Setter or return $this
     * @param string $name
     * @param mixed $value
     * @return target
     */
    public function __set(string $name, $value):target {
        switch($name) {
            case 'id':
                return $this->setID($value);
            case 'owner':
                return $this->setOwner($value);
            case 'content':
                return $this->setContent($value);
            case 'xPos':
                return $this->setXPos($value);
            case 'yPos':
                return $this->setYPos($value);
            case 'map':
                return $this->setMap($value);
            default:
                return $this;
        }
    }

    /**
     * Get via available Getter or return null
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) {
        switch($name) {
            case 'id':
                return $this->getID();
            case 'owner':
                return $this->getOwner();
            case 'content':
                return $this->getContent();
            case 'xPos':
                return $this->getXPos();
            case 'yPos':
                return $this->getYPos();
            case 'map':
                return $this->getMap();
            default:
                return null;
        }
    }

    private function syncVWID() {
        $sql = 'SELECT vw_id FROM udvide.targets WHERE t_id = ?';
        $this->vw_id = access_DB::prepareExecuteFetchStatement($sql,[$this->id])[0]['vw_id'];
    }

    //<editor-fold desc="Fluent Setters with type and permission verification">

    /**
     * @param int $id
     * @return target
     */
    public function setId(int $id = null): target
    {
        if (isset($id)) {
            $this->id = $id;
        }
        return $this;
    }

    /**
     * @param string $owner
     * @return target
     */
    public function setOwner(string $owner = null): target
    {
        if (isset($owner)) {
            $this->owner = $owner; // ToDo after User.php Refactor
        }
        return $this;
    }

    /**
     * @param string $content
     * @return target
     */
    public function setContent(string $content = null): target
    {
        if (isset($content)) {
            $this->content = $content;
        }
        return $this;
    }

    /**
     * @param int $xPos
     * @return target
     */
    public function setXPos(int $xPos = null): target
    {
        if (isset($xPos)) {
            $this->xPos = $xPos;
        }
        return $this;
    }

    /**
     * @param int $yPos
     * @return target
     */
    public function setYPos(int $yPos = null): target
    {
        if (isset($yPos)) {
            $this->yPos = $yPos;
        }
        return $this;
    }

    /**
     * @param string $map
     * @return target
     */
    public function setMap(string $map = null): target
    {
        if (isset($map)) {
            $this->map = $map; // ToDo after map.php
        }
        return $this;
    }

    /**
     * @param resource|string $image
     * @return target
     */
    public function setImage($image = null)
    {
        if (isset($image)) {
            if (is_string($image)) {
                $this->image =
                    imagecreatefromstring(
                        base64_decode(
                            base64ImgToDecodeAbleBase64($image)
                        )
                    );
            } else {
                $this->image = $image;
            }
            $this->image = imgAssistant($this->image, ['maxFileSize' => VUFORIA_DATA_SIZE_LIMIT]);
        }
        return $this;
    }

    /**
     * @param string $name
     * @return target
     */
    public function setName(string $name = null): target
    {
        if (isset($name)) {
            $this->name = purifyValue($name);
        } elseif (empty($name)) {
            // name is not allowed to be empty
            // this solution trades 1:1000000 stability for ease and performance
            $this->name = 'Anonymous Target '. random_int(1000000,9999999);
        }
        if (strlen($this->name) >= VUFORIA_TARGET_NAME_LIMIT) {
            // name has a length limit
            $this->name = substr($this->name, 0, VUFORIA_TARGET_NAME_LIMIT-3) . '...';
        }
        // we do ignore the case that name is both too long and not unique
        return $this;
    }

    /**
     * @param bool $active
     * @return target
     */
    public function setActive(bool $active = null): target
    {
        if (isset($active)) {
            $this->active = $active;
        }
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="Getter with permission verification">
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getXPos(): int
    {
        return $this->xPos;
    }

    /**
     * @return int
     */
    public function getYPos(): int
    {
        return $this->yPos;
    }

    /**
     * @return string
     */
    public function getMap(): string
    {
        return $this->map;
    }

    /**
     * @return resource|string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }
    //</editor-fold>
}