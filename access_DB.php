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
     * This class could offer fallback mechanisms to various DB Access concepts, should one fail
     * @param string $preparesql
     * @param array|null $executesql
     * @return array|false empty -> false
     */
    public static function prepareExecuteFetchStatement($preparesql, $executesql = null)
    {
        //try {
            if (!is_array($executesql) && isset($executesql))
                $executesql[0] = $executesql; // QoL

        echo $preparesql;
        foreach ($executesql as $value)
            echo ',  ' . $value;
        echo '<br/>';

            return dbaccessPDOUdv::prepareExecuteFetchStatement($preparesql, $executesql);
        /*} catch (exception $e) {
            $msg = 'Exception ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . "\n"
                . 'when trying to prepare/execute/fetch query' . $preparesql . "\n"
                . 'continuing to run script with empty result';
            trigger_error($msg);
            return false;
        }*/
    }
}
interface dbaccessUdv {
    /**
     * @param string $preparesql
     * @param array|null $executesql
     * @return array|false empty -> false
     */
    public static function prepareExecuteFetchStatement($preparesql, $executesql = null);
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
     * @param array|null $executesql
     * @return array|false empty -> false
     */
    public static function prepareExecuteFetchStatement($preparesql, $executesql = null)
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

    public static function prepareExecuteFetchStatement($preparesql, $executesql = null)
    {
        $dbaccessobj = null;
        /*$dbaccessobj = */isset(dbaccessSTUBUdv::$singleton) ? dbaccessSTUBUdv::$singleton : new dbaccessSTUBUdv();
        echo 'STUB STUB STUB -- prepared and executed';
        return [['column1'=>'STUBResult1','column2'=>'STUBResult2']];
    }
}
