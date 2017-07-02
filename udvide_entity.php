<?php
require_once 'vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28.06.2017
 * Time: 21:50
 */
abstract class udvide_entity
{
    public abstract static function readAll();

    public abstract function create();
    public abstract function delete();

    /**
     * Fills Entity from array
     * Ignores false calls if __set always returns
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
     *
     * @param string $name
     * @param $value
     * @return mixed an ":static"
     */
    public abstract function __set(string $name, $value);
    public abstract function __get(string $name);
}
