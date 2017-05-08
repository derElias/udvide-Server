<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 26.04.2017
 * Time: 18:01
 */
/**
 * maps a implemented dbaccess
 */

require_once "dbaccessPDOUdv.php";
include_once "dbaccessSTUBUdv.php";

class dbaUdv implements dbaccessUdv {

    /**
     * @param $preparesql
     * @param null $executesql
     * @return mixed
     */
    public static function prepareExecuteGetStatement($preparesql, $executesql = null)
    {
        try {
            return dbaccessPDOUdv::prepareExecuteGetStatement($preparesql, $executesql);
        } catch (exception $e) {
            $msg = 'Exception ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . "\n"
                . 'Caused Fallback to STUB db results';
            trigger_error($msg);
            return dbaccessSTUBUdv::prepareExecuteGetStatement($preparesql, $executesql);
        }
    }
}