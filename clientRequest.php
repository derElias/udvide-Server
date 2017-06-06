<?php
require_once 'udvide.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.04.2017
 * Time: 20:54
 */
header('Content-Type: application/json');
/**
 * will be called like
 * /clientRequest.php?t=[kundennummer][lokale ID]...
 */
$id = $_GET['t'];
$sql = 'SELECT ';
