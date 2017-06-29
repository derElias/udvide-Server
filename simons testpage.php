<?php
require_once 'udvide.php';

echo time();
user::fromDB('root')->setPassHash('imGoingToBePepperedAndSalted')->login();
echo "\n<br/>" . user::getLoggedInUser()->getRole();
echo "\n<br/>";
echo "\n<br/>";
$mt = microtime(true);
$sql = <<<'SQL'
SELECT case when t.deleted = 1 or t.deleted = true then true else false end as deleted,
  owner, content, xPos, yPos, map, vw_id, image, e.uName
FROM udvide.Targets t
JOIN udvide.Editors e
ON t.name = e.tName
WHERE t.name = ?
SQL;
$db = access_DB::prepareExecuteFetchStatement($sql, ["test/t_01_admin"]);

echo microtime(true) - $mt;
