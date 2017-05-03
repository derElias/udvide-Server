<?php
require_once 'c:/xampp/php/pear/HTTP/Request2.php';
require_once 'SignatureBuilder.php';

/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.04.2017
 * Time: 13:16
 */
class vufoenviroment
{
    // on a temp account
    private static $access_key 	    = "[ server access key ]";
    private static $secret_key 	    = "[ server secret key ]";

    private static $targetId 	    = "[ target id ]";

    private static $targetName 	    = "[ tname ]";
    private static $image        	= "[ timage ]";
    private static $width			= 320.0;
    private static $meta			= "tmeta";
    private static $activeflag		= 1;

    function __construct()
    {
        //$this->image = file_get_contents( '/img/logo.png' );
    }

    /**
     * @return string
     */
    public static function getAccessKey(): string
    {
        return self::$access_key;
    }

    /**
     * @return string
     */
    public static function getSecretKey(): string
    {
        return self::$secret_key;
    }

    /**
     * @return string
     */
    public static function getTargetId(): string
    {
        return self::$targetId;
    }

    /**
     * @return string
     */
    public static function getTargetName(): string
    {
        return self::$targetName;
    }

    /**
     * @return string
     */
    public static function getImage(): string
    {
        return self::$image;
    }

    /**
     * @return float
     */
    public static function getWidth(): float
    {
        return self::$width;
    }

    /**
     * @return string
     */
    public static function getMeta(): string
    {
        return self::$meta;
    }

    /**
     * @return int
     */
    public static function getActiveflag(): int
    {
        return self::$activeflag;
    }
}