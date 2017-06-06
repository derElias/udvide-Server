<?php
require_once 'udvide.php';

echo time();

$udvide = new udvide();
// $target = (new target())->setId(5)->setVwId('12cf02818ddb43029724f018c02e3efd')->setContent('{"text":"Bye World!"}');
// $udvide->doTargetManipulationAs('update',$target,'test/tAdmin','iAmBad');
// $target2 = (new target())->setId(3)->setVwId('1b78507caa16449f84535978a794fc94');
// $hr = $udvide->doTargetManipulationAs('deactivate',$target2,'test/tAdmin','iAmBad');
// var_dump($hr);
$pageOne = $udvide->getTargetPageByUser('test/tEditor');
var_dump($pageOne);
