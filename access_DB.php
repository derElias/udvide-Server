<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 26.04.2017
 * Time: 18:01
 */

require_once "enviromentUdv.php";

class access_DB implements dbaccessUdv {

    /**
     * This class should offer fallback mechanisms to various DB Access concepts, should one fail
     * @param string $preparesql
     * @param string|null $executesql
     * @return array|false empty -> false
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
interface dbaccessUdv {
    /**
     * @param string $preparesql
     * @param string|null $executesql
     * @return array|false empty -> false
     */
    public static function prepareExecuteGetStatement($preparesql, $executesql = null);
}

class dbaccessPDOUdv implements dbaccessUdv
{
    private static $singleton;
    private $connection;

    /**
     * dbaccessPDO constructor.
     */
    private function __construct()
    {
        $enviroment = new enviromentUdv();
        $this->connection = new PDO(
            'mysql:host=' . $enviroment->getSqldbservername()
            . ';dbname='  . $enviroment->getSqldbname()
            . ';charset=' . $enviroment->getSqlCharset(),
            $enviroment->getSqldbusername(), $enviroment->getSqldbpassword());
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Docu: Recommended in php versions more recent then 5.1 according to http://stackoverflow.com/questions/10113562/pdo-mysql-use-pdoattr-emulate-prepares-or-not
    }

    public function __destruct()
    {
        $this->connection = NULL;
    }

    /**
     * @param string $preparesql
     * @param string|null $executesql
     * @return array|false empty -> false
     */
    public static function prepareExecuteGetStatement($preparesql,$executesql = null)
    {
        $dbaccessobj = isset(self::$singleton) ? self::$singleton : new dbaccessPDOUdv();
        $stmt = $dbaccessobj->connection->prepare($preparesql);
        $stmt->execute($executesql);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = NULL;
        return empty($rows) || $rows === false ? false : $rows;
    }
}

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
        echo 'STUB STUB STUB -- prepared and executed';
        return [['column1'=>'STUBResult1','column2'=>'STUBResult2']];
    }
}
