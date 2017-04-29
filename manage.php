<?php
require_once 'vuforiaaccess.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.04.2017
 * Time: 20:55
 */

/**
 * init
 */

/**
 * get home template
 */
$template = file_get_contents('managetemplate.html');
//$template = str_replace('{}', $elem,$template);

echo $template;

echo (new vuforiaaccess())->execute();

