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
echo "\n";
/**
 * get home template
 */
$template = file_get_contents('managetemplate.html');
$template = str_replace('{HOME}', (new vuforiaaccess())->execute(),$template);

echo $template;