<?php
// User friendly Settings
define('LANGUAGE','en');

// Where am i?
$host= gethostname();
$ip = gethostbyname($host);
define('EXTERNAL_BASE_PATH',$ip);
define('INTERNAL_BASE_PATH','C:\Users\User\Documents\udvide-Server');

// Where is my DB?
define('SQL_DB_SERVER','localhost');
define('SQL_DB_USERNAME','root');
define('SQL_DB_PASSWORD','');
define('SQL_DB_NAME','udvide');
define('SQL_DB_CHARSET','utf8mb4');

// Who is who?
define('PERMISSIONS_ROOT',5);
define('PERMISSIONS_DEVELOPER',4);
define('PERMISSIONS_CLIENT',3);
define('PERMISSIONS_ADMIN',2);
define('PERMISSIONS_EDITOR',1);

// Who is allowed to do what?
//  Targets
define('MIN_ALLOW_TARGET_ASSIGN',PERMISSIONS_ADMIN);
define('MIN_ALLOW_TARGET_CREATE',PERMISSIONS_ADMIN);
define('MIN_ALLOW_TARGET_UPDATE',PERMISSIONS_ADMIN);
define('ALLOW_ASSIGNED_TARGET_UPDATE',true);
define('MIN_ALLOW_TARGET_DELETE',PERMISSIONS_ADMIN);
define('ALLOW_ASSIGNED_TARGET_DELETE',false);
//  Users
define('MIN_ALLOW_USER_CREATE',PERMISSIONS_ADMIN);
define('MIN_ALLOW_USER_UPDATE',PERMISSIONS_ADMIN); //ToDo
define('ALLOW_SELF_UPDATE',true); //ToDo
define('MIN_ALLOW_USER_DELETE',PERMISSIONS_ADMIN);
define('ALLOW_SELF_DELETE',true); //ToDo
//  Maps
define('MIN_ALLOW_MAP_CREATE',PERMISSIONS_ADMIN);
define('MIN_ALLOW_MAP_UPDATE',PERMISSIONS_ADMIN);
define('MIN_ALLOW_MAP_DELETE',PERMISSIONS_ADMIN);

// What do others expect from us?
define('VUFORIA_DATA_SIZE_LIMIT', 2000000); // =IMG=META
define('VUFORIA_TARGET_NAME_LIMIT', 64);

// Internal Settings
// Error codes ToDo
$errLang = json_decode(file_get_contents('errLang_'.LANGUAGE.'.json'));
foreach ($errLang as $key=>$value) {
    define('ERR_' . $key, $value);
}
define('MAP_WIDTH',1000);