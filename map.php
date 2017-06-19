<?php
require_once 'helper.php';

/**
 * Created by PhpStorm.
 * User: User
 * Date: 17.06.2017
 * Time: 18:30
 */
class map
{
    /** @var  string */
    private $name;
    /** @var  resource */
    private $image;

    //<editor-fold desc="Constructors">
    public function __construct(){}

    public function fromDB(string $name):map
    {
        $instance = new self();
        if (isset($name)) {
            $instance->setName($name)->read();
        }
        return $instance;
    }

    public function fromArray(array $array = null) {
        $instance = new self();
        if (!empty($array))
            $instance->set($array);
        return $instance;
    }
    //</editor-fold>

    //<editor-fold desc="CRUD">
    public function read()
    {
        $sql = <<<'SQL'
SELECT name, image
FROM udvide.Maps m
WHERE name = ?
SQL;
        return access_DB::prepareExecuteFetchStatement($sql, [$this->name]);
    }

    public static function readAll()
    {
        $sql = <<<'SQL'
SELECT name, image
FROM udvide.Maps m
SQL;
        $db = access_DB::prepareExecuteFetchStatement($sql);
        foreach ($db as $key => $userArr)
            $db[$key] = (new self())->set($userArr);
        return $db;
    }

    public function create()
    {
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_MAP_CREATE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);
        if (!isset($this->name) || !isset($this->image))
            throw new IncompleteObjectException(ERR_USER_DATASET_INVALID,1);

        $sql = <<<'SQL'
INSERT INTO udvide.Maps
(name,image)
VALUES (?,?);
SQL;
        $values = [
            $this->name,
            $this->getImageAsRawJpg()
        ];
        access_DB::prepareExecuteStatementGetAffected($sql,$values);
        return $this;
    }

    public function update()
    {
        // If not allowed to update and self-update (in case of self update)
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_MAP_UPDATE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $updateDB = false;
        $sql = '';
        foreach ($this as $key => $value) {
            if(isset($this->{$key})) {
                $sql .= " $key = ? , ";
                $ins[] = $value;
                $updateDB = true;
            }
        }

        if ($updateDB) {
            $sql = <<<SQL
UPDATE udvide.maps
SET
$sql
name = name
WHERE name = ?;
SQL;
            $ins[] = $this->name;
            access_DB::prepareExecuteFetchStatement($sql, $ins);
        }
        return $this;
    }

    public function delete()
    {
        if (user::getLoggedInUser()->getRole() < MIN_ALLOW_MAP_DELETE)
            throw new PermissionException(ERR_PERMISSION_INSUFFICIENT,1);

        $sql = 'DELETE FROM udvide.Maps WHERE name = ?';
        access_DB::prepareExecuteFetchStatement($sql,[$this->name]);
    }
    //</editor-fold>

    public function set(array $data)
    {
        foreach ($data AS $key => $value) {
            $this->__set($key, $value); // To use setters behind permission and type verification
        }
        return $this;
    }

    public function __set(string $name, $value): map
    {
        switch($name) {
            case 'name':
                return $this->setName($value);
            case 'image':
                return $this->setImage($value);
            default:
                return $this;
        }
    }

    public function __get(string $name) {
        switch($name) {
            case 'name':
                return $this->getName();
            case 'image':
                return $this->getImage();
            case 'width':
                return $this->getWidth();
            case 'height':
                return $this->getHeight();
            default:
                return null;
        }
    }

    //<editor-fold desc="Fluent Setter">

    /**
     * @param string $name
     * @return map
     */
    public function setName(string $name = null):map
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $image
     * @return map
     */
    public function setImage(string $image = null):map
    {
        $this->image = $image;
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="Getter">
    /**
     * @return string
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * @return resource
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
    //</editor-fold>

}