<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.04.2017
 * Time: 13:16
 */
class enviroment
{
    private static $access_key 	    = "[ server access key ]";
    private static $secret_key 	    = "[ server secret key ]";

    private static $targetId 	    = "[ target id ]";

    private static $targetName 	    = "[ name ]";
    private static $imageLocation 	= "[ /path/file.ext ]";
    private static $width			= 320.0;
    private static $meta			= "Vuforia test metadata";
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
}