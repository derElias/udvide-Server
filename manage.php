<?php
$template = file_get_contents('temp/manageTempl.html');

    $svg = file_get_contents('res/home.svg');
    $template = str_replace('<!--home-->',$svg,$template);
    $svg = file_get_contents('res/Entry.svg');
    $template = str_replace('<!--eintrag-->',$svg,$template);


$temp = file_get_contents('temp/MitarbeiterTempl.html');
$template = str_replace('<!--Mitarbeiter-->',$temp,$template);

    $svg = file_get_contents('res/Marker.svg');
    $template = str_replace('<!--marker-->',$svg,$template);



$temp = file_get_contents('temp/AdminTempl.html');
$template = str_replace('<!--Admin-->',$temp,$template);

    $svg = file_get_contents('res/User.svg');
    $template = str_replace('<!--verwaltung-->',$svg,$template);
    $svg = file_get_contents('res/Map.svg');
    $template = str_replace('<!--karten-->',$svg,$template);


$temp = file_get_contents('temp/contentTempl.html');
$template = str_replace('<!--content-->',$temp,$template);

$temp = file_get_contents('temp/footerTempl.html');
$template = str_replace('<!--footer-->',$temp,$template);

echo $template;
?>






