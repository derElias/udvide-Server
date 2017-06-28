<?php
require_once 'helper.php';
require_once 'user.php';
require_once 'map.php';
require_once 'target.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 19.06.2017
 * Time: 22:10
 */
abstract class udvide {

    //<editor-fold desc="Constructors">
    /**
     * indirect constructor
     * @param string $name
     * @return static
     */
    public static function fromDB(string $name = '') {
        $instance = new static();
        if (!empty($name)) {
            $instance->setName($name)->read(); // phpstorm bug see https://stackoverflow.com/questions/44803353/returntype-self-in-abstract-php-class/44803407?noredirect=1
        }
        return $instance;
    }

    /**
     * indirect constructor
     * @param array|null $array
     * @return static
     */
    public static function fromArray(array $array = null) {
        $instance = new static();
        if (!empty($array))
            $instance->set($array);
        return $instance;
    }

    /**
     * indirect constructor
     * @param string $json
     * @return static
     */
    public static function fromJSON(string $json = '') {
        $instance = new static();
        if (!empty($json))
            $instance->set(json_decode($json, true));
        return $instance;
    }
    //</editor-fold>

    public abstract static function readAll();

    public abstract function read();
    public abstract function create();
    public abstract function update(string $subject = null);
    public abstract function delete();

    public function set(array $data)
    {
        foreach ($data AS $key => $value) {
            $this->__set($key, $value); // To use setters behind permission and type verification
        }
        return $this;
    }

    public abstract function __set(string $name, $value);
    public abstract function __get(string $name);

    public abstract function setName(string $name);
}