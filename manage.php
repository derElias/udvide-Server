<?php
$template = file_get_contents('templates/manageTempl.html');

/*
$temp = file_get_contents('templates/AdminTempl.html');
$template = str_replace('<!--Admin-->',$temp,$template);

*/

$temp = file_get_contents('templates/home.html');
$template = str_replace('<!--content-->',$temp,$template);



$temp = file_get_contents('templates/footerTempl.html');
$template = str_replace('<!--footer-->',$temp,$template);

echo $template;
?>
