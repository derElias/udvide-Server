<?php
require_once 'vendor/autoload.php';


abstract class udvide extends udvide_entity implements JsonSerializable
{
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

    public abstract function read();
    public abstract function update(string $subject = null);

    public abstract function setName(string $name);


    /**
     * Because JSON serialization doesn't allow Image resources they default to the DataURI JPG string
     *
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $assoc = [];
        foreach ($this as $field => $value) {
            if (isset($this->{$field})) {
                if (is_resource($value)) {
                    // image handling
                    $assoc[$field] = 'data:image/jpeg;base64,' . base64_encode(helper::imgResToJpgString($value));
                } else {
                    $assoc[$field] = $this->__get($field);
                }
            }
        }
        return $assoc;
    }
}
