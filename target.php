<?php
require_once 'udvideV3.php';
/**
 * Created by PhpStorm.
 * Date: 14.06.2017 Refactor in new file
 * Time: 15:48
 */
class target
{
    // SQL only Values
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
    /** @var  string */
    private $name;

    // VWS only Values
    /** @var  bool */
    private $active;

    // VWS generated Values
    // ToDo

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

    //<editor-fold desc="CRUD DB">
    public function read() {
        $sql = <<<'SQL'
SELECT case when t.deleted = 1 or t.deleted = true then true else false end as deleted, owner, content, xPos, yPos, map, vw_id, image
FROM udvide.Targets t
WHERE name = ?
SQL;
        $db = access_DB::prepareExecuteFetchStatement($sql, [$this->name]);
        $this->set($db[0]);
        return $this;
    }

    public static function readAll() {
        $sql = <<<'SQL'
SELECT name, owner, content, xPos, yPos, map, vw_id, image
FROM udvide.Targets
WHERE deleted = 0 or deleted = false
SQL;
        $db = access_DB::prepareExecuteFetchStatement($sql);
        foreach ($db as $key => $userArr)
            $db[$key] = (new self())->set($userArr);
        return $db;
    }

    public function create() {
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_TARGET_CREATE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);
        if (!isset($this->name))
            throw new IncompleteObjectException(ERR_USER_DATASET_INVALID,1); // How tf did u do dis?

        $sql = <<<'SQL'
INSERT INTO udvide.Targets
(name, owner)
VALUES (?,?);
SQL;
        $values = [
            $this->name,
            isset($this->owner) ? $this->owner : user::getLoggedInUser()->getUsername()
        ];
        access_DB::prepareExecuteStatementGetAffected($sql,$values);

        $vwsResponse =  $this->pvfupdateobject() // amusingly with enough refactoring even a create is suddenly just another update
            ->setMeta('/clientRequest.php?t=' . base64_encode($this->name))
            ->setAccessMethod('create')
            ->execute();

        $vwsResponseBody = json_decode($vwsResponse->getBody());
        $this->vw_id = $vwsResponseBody->target_id;
        $tr_id = $vwsResponseBody->transaction_id;

        logTransaction($tr_id,user::getLoggedInUser()->getUsername(),$this->name);

        return $this;
    }

    public function update(string $subject = null) {
        // If not allowed to update and self-update (in case of self update)
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_TARGET_UPDATE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $subject = empty($subject) ? $subject : $this->name;

        $this->pdbupdate($subject);

        if (isset($this->name) || isset($this->image) || isset($this->active)) {
            $vwsResponse = $this->pvfupdateobject()->execute();
            $vwsResponseBody = json_decode($vwsResponse->getBody());
            $this->vw_id = $vwsResponseBody->target_id;
            $tr_id = $vwsResponseBody->transaction_id;

            logTransaction($tr_id,user::getLoggedInUser()->getUsername(),$this->name);
        }
        return $this;
    }

    private function pdbupdate($subject)
    {
        // If not allowed to update and self-update (in case of self update)
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_MAP_UPDATE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT, 1);

        $updateDB = false;
        $sql = '';
        foreach ($this as $key => $value) {
            if ($key != 'active'
                && strpos($key, 'vwgen_') !== 0
                && isset($this->{$key})
            ) {
                $sql .= " $key = ? , ";
                $ins[] = $value;
                $updateDB = true;
            }
        }

        if ($updateDB) {
            $sql = <<<SQL
DECLARE @dummy int;
UPDATE udvide.Targets
SET
$sql
@dummy = 0
WHERE name = ?;
SQL;
            $ins[] = $subject;
            access_DB::prepareExecuteFetchStatement($sql, $ins);
        }
    }

    private function pvfupdateobject()
    {
        $vwsa = (new access_vfc())
            ->setTargetId($this->vw_id)
            ->setAccessMethod('update')
            ->setTargetName(isset($this->name) ? $this->name : null)
            ->setImage(isset($this->image) ? $this->getImageAsRawJpg() : null)
            ->setActiveflag(isset($this->active) ? $this->active : null);

        return $vwsa;
    }

    public function deactivate() {
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_TARGET_DEACTIVATE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $this->deleted = true;
        $this->pdbupdate($this->name);
    }
    public function delete() {
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_TARGET_DELETE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        if (!$this->deleted) { // ToDo: Is this flawed? do we always have a set deleted when trying to delete?
            // never delete directly
            $this->deactivate();
            return;
        }
        $sql = <<<'SQL'
SELECT vw_id
FROM udvide.Targets 
WHERE name = ?;

DELETE FROM udvide.Targets
WHERE name = ?;
SQL;
        access_DB::prepareExecuteFetchStatement($sql,[$this->name,$this->name]);

    }
    //</editor-fold>

    /**
     * Fills the target from an array
     * @param array $data
     * @return $this
     */
    public function set(array $data)
    {
        foreach ($data AS $key => $value) {
            $this->__set($key, $value); // To use setters behind permission and type verification
        }
        return $this;
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
            $this->map = $map;
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
            $this->name = $name;
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
    public function getImageAsRawJpg()
    {
        return imgResToJpgString($this->image); // quality defaults to 95
    }

    /**
     * @return string
     */
    public function getImageAsBase64Jpg()
    {
        return base64_encode($this->getImageAsRawJpg());
    }

    /**
     * @return string
     */
    public function getImageAsDataUrlJpg()
    {
        return 'data:image/jpeg;base64,' . $this->getImageAsBase64Jpg();
    }

    /**
     * @return int
     */
    public function getHeight():int
    {
        return imagesy($this->image);
    }

    /**
     * @return int
     */
    public function getWidth():int
    {
        return imagesx($this->image);
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