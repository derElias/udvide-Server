<?php
require_once "../enviromentUdv.php";
require_once "dbaccessUdv.php";

class dbaccessPDOUdv implements dbaccessUdv
{
    private static $singleton;
    private $connection;

    /**
     * dbaccessPDO constructor.
     */
    private function __construct()
    {
        $enviroment = new enviroment();
        $this->connection = new PDO(
            'mysql:host=' . $enviroment->getSqldbservername()
            . ';dbname='  . $enviroment->getSqldbname(),
            $enviroment->getSqldbusername(), $enviroment->getSqldbpassword());
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function __destruct()
    {
        $this->connection = NULL;
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
        $dbaccessobj = isset(self::$singleton) ? self::$singleton : new dbaccessPDOUdv();
        $stmt = $dbaccessobj->connection->prepare($preparesql);
        $stmt->execute($executesql);
        $rows = $stmt->fetchAll();
        $stmt = NULL;
        return $rows === false ? false : $rows;
    }
}