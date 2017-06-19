<?php
require_once 'helper.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 19.06.2017
 * Time: 17:03
 */
if ($_SERVER["REQUEST_METHOD"] == "POST" && !GET_INSTEAD_POST
    || $_SERVER["REQUEST_METHOD"] == "GET" && GET_INSTEAD_POST)
{
    $path = 'res';
    $fullFileArray = scandir($path,SCANDIR_SORT_NONE);
    $fileArray = array_diff($fullFileArray, array('..', '.')); // scan $path and get rid of . and .. (picked up on linux)
    foreach ($fileArray as $file) {
        $filePath = $path . DIRECTORY_SEPARATOR . $file;
        $res[$file] = sanitizeXML(file_get_contents($filePath));
    }

    $path = 'templates';
    $fullFileArray = scandir($path,SCANDIR_SORT_NONE);
    $fileArray = array_diff($fullFileArray, array('..', '.'));
    foreach ($fileArray as $file) {
        $filePath = $path . DIRECTORY_SEPARATOR . $file;
        $templ[$file] = sanitizeXML(file_get_contents($filePath));
    }

    $package = [
        'res' => isset($res) ? $res : '',
        'templates' => isset($templ) ? $templ : ''
    ];
    
    echo json_encode($package);

} else {
    echo "This site is providing a Resource Package for the Javascript application in <a href='manage.php'>the main site!</a> <br/>
            Consult the Documentation for more information";
}
