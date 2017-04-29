<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.04.2017
 * Time: 13:16
 */
class vufoenviroment
{
    private static $access_key 	    = "[ server access key ]";
    private static $secret_key 	    = "[ server secret key ]";

    private static $targetId 	    = "[ target id ]";

    private static $targetName 	    = "[ fehlername ]";
    private static $imageLocation 	= "[ /path/fehlerfile.ext ]";
    private static $width			= 320.0;
    private static $meta			= "Vuforia test fehlermetadata";
    private static $activeflag		= 1;

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
    public static function getImageLocation(): string
    {
        return self::$imageLocation;
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