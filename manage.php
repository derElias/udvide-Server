<?php
$template = file_get_contents('templates/manageTempl.html');

$temp = file_get_contents('templates/header.html');
$template = str_replace('<!--header-->',$temp,$template);

$temp = file_get_contents('templates/entrytableTempl.html');
$template = str_replace('<!--content-->',$temp,$template);



$temp = file_get_contents('templates/footerTempl.html');
$template = str_replace('<!--footer-->',$temp,$template);

echo $template;
?>
