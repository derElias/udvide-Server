<?php
require_once '../access_DB.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 17.05.2017
 * Time: 17:58
 */
echo access_DB::prepareExecuteGetStatement('SELECT * FROM users');
