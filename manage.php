<?php
$template = file_get_contents('temp/manageTempl.html');

    $svg = file_get_contents('res/Home.svg');
    $template = str_replace('<!--home-->',$svg,$template);
    $svg = file_get_contents('res/Entry.svg');
    $template = str_replace('<!--eintrag-->',$svg,$template);


$temp = file_get_contents('temp/AdminTempl.html');
$template = str_replace('<!--Admin-->',$temp,$template);

    $svg = file_get_contents('res/User.svg');
    $template = str_replace('<!--verwaltung-->',$svg,$template);
    $svg = file_get_contents('res/Map.svg');
    $template = str_replace('<!--karten-->',$svg,$template);


$temp = file_get_contents('temp/entrytableTempl.html');
$template = str_replace('<!--content-->',$temp,$template);

    $svg = file_get_contents('res/Create.svg');
    $template = str_replace('<!--Create-->',$svg,$template);

    $svg = file_get_contents('res/Delete.svg');
    $template = str_replace('<!--Delete-->',$svg,$template);

    $svg = file_get_contents('res/Entry.svg');
    $template = str_replace('<!--Update-->',$svg,$template);



$temp = file_get_contents('temp/contentTempl.html');
$template = str_replace('<!--content-->',$temp,$template);

    $svg = file_get_contents('res/search.svg');
    $template = str_replace('<!--searchicon-->',$svg,$template);

$temp = file_get_contents('temp/footerTempl.html');
$template = str_replace('<!--footer-->',$temp,$template);

echo $template;
?>
