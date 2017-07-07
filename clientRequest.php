<?php
require_once 'vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.04.2017
 * Time: 20:54
 */
header('Content-Type: application/text');
/**
 * will be called like
 * /clientRequest.php?t=[base64encodedTargetName]
 */
$name = base64_decode($_GET['t']);
$t = target::fromDB($name);
// onMobileRead($t);
return $t->getContent();
