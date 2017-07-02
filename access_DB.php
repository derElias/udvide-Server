<?php
require_once 'vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 26.04.2017
 * Time: 18:01
 */

class access_DB implements dbaccessUdv {

    /**
     * This class could offer fallback mechanisms to various DB Access concepts, should one fail
     * @param string $preparesql
     * @param array|null $executesql
     * @return array|false empty -> false
     */
    public static function prepareExecuteFetchStatement(string $preparesql, $executesql = null)
    {
        if (DEBUG_ACCESS_DB) {
            var_dump($preparesql);
            var_dump($executesql);
        }
        if (!is_array($executesql) && isset($executesql))
            $executesql[0] = $executesql; // QoL
        return dbaccessPDOUdv::prepareExecuteFetchStatement($preparesql,$executesql);
    }

    public static function prepareStatement(string $preparesql)
    {
        //try {
        return dbaccessPDOUdv::prepareStatement($preparesql);
        /*} catch (exception $e) {
            $msg = 'Exception ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . "\n"
                . 'when trying to prepare/execute/fetch query' . $preparesql . "\n"
                . 'continuing to run script with empty result';
            trigger_error($msg);
            return false;
        }*/
    }

    public static function executeFetchStatement($executesql = null)
    {
        //try {
        if (!is_array($executesql) && isset($executesql))
            $executesql[0] = $executesql; // QoL
        return dbaccessPDOUdv::executeFetchStatement($executesql);
        /*} catch (exception $e) {
            $msg = 'Exception ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . "\n"
                . 'when trying to prepare/execute/fetch query' . $preparesql . "\n"
                . 'continuing to run script with empty result';
            trigger_error($msg);
            return false;
        }*/
    }

    public static function prepareExecuteStatementGetAffected(string $preparesql, $executesql = null)
    {
        if (!is_array($executesql) && isset($executesql))
            $executesql[0] = $executesql; // QoL
        return dbaccessPDOUdv::prepareExecuteStatementGetAffected($preparesql,$executesql);
    }
}
interface dbaccessUdv {
    /**
     * @param string $preparesql
     * @param array|null $executesql
     * @return array|false empty -> false
     */
    public static function prepareExecuteFetchStatement(string $preparesql, $executesql = null);

    public static function prepareExecuteStatementGetAffected(string $preparesql, $executesql = null);

    public static function prepareStatement(string $preparesql);

    public static function executeFetchStatement($executesql = null);
}

class dbaccessPDOUdv implements dbaccessUdv
{
    private static $singleton;
    private $connection;
    private $statement;

    /**
     * dbaccessPDO constructor.
     */
    private function __construct()
    {
        $this->connection = new PDO(
            'mysql:host=' . SQL_DB_SERVER
            . ';dbname='  . SQL_DB_NAME
            . ';charset=' . SQL_DB_CHARSET,
            SQL_DB_USERNAME, SQL_DB_PASSWORD);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Docu: Recommended in php versions more recent then 5.1 according to http://stackoverflow.com/questions/10113562/pdo-mysql-use-pdoattr-emulate-prepares-or-not
    }

    public function __destruct()
    {
        $stmt = NULL;
        $this->connection = NULL;
    }

    /**
     * @param string $preparesql
     * @param array|null $executesql
     * @return array|false empty -> false
     */
    public static function prepareExecuteFetchStatement(string $preparesql, $executesql = null)
    {
        self::prepareStatement($preparesql);
        return self::executeFetchStatement($executesql);
    }

    public static function prepareStatement(string $preparesql)
    {
        self::$singleton = isset(self::$singleton) ? self::$singleton : new dbaccessPDOUdv();
        self::$singleton->statement = self::$singleton->connection->prepare($preparesql);
        return true;
    }

    public static function executeFetchStatement($executesql = null)
    {
        if (!isset(self::$singleton)) {
            throw new Exception('prepare statement before executing!');
        }
        $dbaccessobj = self::$singleton;
        $dbaccessobj->statement->execute($executesql);

        $rows = $dbaccessobj->statement->fetchAll(PDO::FETCH_ASSOC);
        return empty($rows) || $rows === false ? false : $rows;

    }

    /**
     * @param string $preparesql
     * @param null $executesql
     * @return mixed false if empty last ID if successful
     */
    public static function prepareExecuteStatementGetAffected(string $preparesql, $executesql = null)
    {
        self::prepareStatement($preparesql);
        $dbaccessobj = self::$singleton;
        $dbaccessobj->statement->execute($executesql);
        $sql = 'SELECT LAST_INSERT_ID() lastId;'; // Docu: this works as long as no one is using the same connection (aka this singleton) async
        $dbaccessobj->statement=$dbaccessobj->connection->prepare($sql);
        $dbaccessobj->statement->execute();
        $rows = $dbaccessobj->statement->fetchAll(PDO::FETCH_ASSOC);
        return empty($rows) || $rows === false ? false : $rows[0]['lastId'];
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

    public static function prepareExecuteFetchStatement(string $preparesql, $executesql = null)
    {
        $dbaccessobj = null;
        /*$dbaccessobj = */isset(dbaccessSTUBUdv::$singleton) ? dbaccessSTUBUdv::$singleton : new dbaccessSTUBUdv();
        echo 'STUB STUB STUB -- prepared and executed';
        return [['column1'=>'STUBResult1','column2'=>'STUBResult2']];
    }

    public static function prepareStatement(string $preparesql)
    {
        echo 'STUB prepared';
    }

    public static function executeFetchStatement($executesql = null)
    {
        echo 'STUB Executed';
        echo 'STUB Fetched false';
        return false;
    }

    public static function prepareExecuteStatementGetAffected(string $preparesql, $executesql = null)
    {
        echo 'STUB tried to DML returning false';
        return false;
    }
}
