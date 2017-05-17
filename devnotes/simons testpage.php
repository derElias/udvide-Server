<?php
require_once '../dbaccess/dbaccessPDOUdv.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 16.05.2017
 * Time: 17:09
 */
echo time();

$sql = /** @lang mysql */
    <<<'VERYLONGTAGTONOTBESTUPID'
SELECT *
FROM Users
VERYLONGTAGTONOTBESTUPID;
echo $sql;
$array = dbaccessPDOUdv::prepareExecuteGetStatement($sql);
