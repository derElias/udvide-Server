<?php
require_once '../access_DB.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 16.05.2017
 * Time: 17:09
 */
echo time();

$var = access_DB::prepareExecuteGetStatement('SELECT ? FROM users','*');
var_dump($var);
