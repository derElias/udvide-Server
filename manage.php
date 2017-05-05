<?php
$template = file_get_contents('temp/manageTempl.html');

$temp = file_get_contents('temp/menuTempl.html');
$template = str_replace('<!--menu-->',$temp,$template);

    $svg = file_get_contents('res/home.svg');
    $template = str_replace('<!--home-->',$svg,$template);
    $svg = file_get_contents('res/home.svg');
    $template = str_replace('<!--eintrag-->',$svg,$template);
    $svg = file_get_contents('res/home.svg');
    $template = str_replace('<!--marker-->',$svg,$template);
    $svg = file_get_contents('res/home.svg');
    $template = str_replace('<!--verwaltung-->',$svg,$template);
    $svg = file_get_contents('res/home.svg');
    $template = str_replace('<!--karten-->',$svg,$template);

$temp = file_get_contents('temp/contentTempl.html');
$template = str_replace('<!--content-->',$temp,$template);

echo $template;
?>






