<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 19.06.2017
 * Time: 17:03
 */
if ($_SERVER["REQUEST_METHOD"] == "POST" && !GET_INSTEAD_POST
    || $_SERVER["REQUEST_METHOD"] == "GET" && GET_INSTEAD_POST) {

    $path = '/res';
    foreach (scandir($path) as $file) {
        $filePath = $path . $file;
        $res[$file] = file_get_contents($filePath);
    }

    $path = '/templates';
    foreach (scandir($path) as $file) {
        $filePath = $path . $file;
        $templ[$file] = file_get_contents($filePath);
    }

    $package = [
        'res' => isset($res) ? $res : '',
        'templates' => isset($templ) ? $templ : ''
    ];
    
    return json_encode($package);

} else {
    echo "This site is providing a Resource Package for the Javascript application in <a href='manage.php'>the main site!</a> <br/>
            Consult the Documentation for more information";
}