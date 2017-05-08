<?php
require_once "../enviromentUdv.php";
require_once "dbaccessUdv.php";

class dbaccessSTUBUdv implements dbaccessUdv
{
    private static $singleton;
//    private $connection;

    /**
     * dbaccessPDO constructor.
     */
    private function __construct()
    {
        echo 'construct';
    }

    public function __destruct()
    {
        echo 'destruct';
    }

    /**
     * @param string $preparesql
     * @param mixed $executesql string or null
     * @return mixed false on failure or rows on success
     * @internal param $sql
     */
    public static function prepareExecuteGetStatement($preparesql,$executesql = null)
    {
        $dbaccessobj = null;
        /*$dbaccessobj = */isset(dbaccessSTUBUdv::$singleton) ? dbaccessSTUBUdv::$singleton : new dbaccessSTUBUdv();
        echo 'prepared and executed';
        return ['STUBResult1','STUBResult2'];
    }
}