<?php
$template = file_get_contents('templates/manageTempl.html');

    $svg = file_get_contents('res/Home.svg');
    $template = str_replace('<!--home-->',$svg,$template);
    $svg = file_get_contents('res/Entry.svg');
    $template = str_replace('<!--eintrag-->',$svg,$template);


$temp = file_get_contents('templates/AdminTempl.html');
$template = str_replace('<!--Admin-->',$temp,$template);

    $svg = file_get_contents('res/User.svg');
    $template = str_replace('<!--verwaltung-->',$svg,$template);
    $svg = file_get_contents('res/Map.svg');
    $template = str_replace('<!--karten-->',$svg,$template);

/*
$temp = file_get_contents('templates/entrytableTempl.html');
$template = str_replace('<!--content-->',$temp,$template);
    if(true) {
        $temp = file_get_contents('templates/Entry.html');
        $template = str_replace('<!--Entry-->', $temp, $template);
    }
    else {
        $temp = file_get_contents('templates/noEntryFoundTempl.html');
        $template = str_replace('<!--Entry-->', $temp, $template);
    }
    $svg = file_get_contents('res/Create.svg');
    $template = str_replace('<!--Create-->',$svg,$template);

    $svg = file_get_contents('res/Delete.svg');
    $template = str_replace('<!--Delete-->',$svg,$template);

    $svg = file_get_contents('res/Entry.svg');
    $template = str_replace('<!--Update-->',$svg,$template);

    $svg = file_get_contents('res/search.svg');
    $template = str_replace('<!--searchicon-->',$svg,$template);

*/
$temp = file_get_contents('templates/contentTempl.html');
$template = str_replace('<!--content-->',$temp,$template);



$temp = file_get_contents('templates/footerTempl.html');
$template = str_replace('<!--footer-->',$temp,$template);

echo $template;
?>
