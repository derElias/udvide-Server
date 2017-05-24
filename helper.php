<?php
require_once 'access_DB.php';
require_once 'access_vfc.php';

/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.05.2017
 * Time: 16:12
 */

/**
 * @return array
 */
function purifyUserData():array {
    // if every input gets the same treatment, operations on the server side
    // should always give the same result for identical client input
    $result = [];
    foreach ($_POST as $item => $value) {
        $result[$item] = htmlspecialchars(stripslashes(trim($_POST[$item])));
    }
    return $result;
}

/**
 * @param string $userPassHash
 * @param string $serverPassHash
 * @return bool
 */
function pepperedPassCheck(string $userPassHash,string $serverPassHash):bool
{
    $keys = json_decode(file_get_contents('keys.json'));
    return password_verify(sha1($userPassHash . $keys->pepper), $serverPassHash);
}

/**
 * @param string $user
 * @param string $pass
 * @return bool|array false on invalid login, integer if admin/client or array of targetIds
 */
function getPermissions(string $user, string $pass)
{
    $sql = <<<'SQL'
SELECT u.passHash, u.role, e.t_id
FROM udvide.Users u
LEFT JOIN Editors e
ON u.username = e.username
WHERE u.username = ?
SQL;
    $db = access_DB::prepareExecuteFetchStatement($sql, [$user]); // Documentation: this is how to follow Don't trust the user with dbaccess in addition to purify
    if ($db === false)
        return false; // user doesn't exist
    if (!pepperedPassCheck($pass, $db[0]['passHash']))
        return false; // password incorrect
    if ($db[0]['role'] > 0)
        return [$db[0]['role']]; // returns role if not editor 1:admin 2:client
    $allMarkers = [];
    foreach ($db as $i=>$row) { // since each row is indexed with an integer (starting at 0) $i will just iterate
        $allMarkers[$i] = $row['t_id'];
    }
    return [0,$allMarkers]; // return an array like [0,['tid1','tid2']]
}

/**
 * @param string $imgString accepts gd2, gd2part, gd, gif, png, wbmp, webp, xbm and xpm. bmp supported if php 7.2.0+ is used
 * @param array $options supports quality, maxFileSize, doNotCrop, minQuality, minShortestSide
 * @return string|false (smaller) JPG | false on failure
 */
function jpgAssistant (string $imgString, array $options):string {
    // to image instance
    $img = imagecreatefromstring($imgString);

    // get options
    $quality = 100;
    $minQ = 5;
    if (isset($options['quality'])) {
        $quality = $options['quality'];
        $minQ = $quality;
    }
    // file size reduction mode
    if (isset($options['maxFileSize'])) {
        $maxFSize = $options['maxFileSize'];

        $doNotCrop = false;
        $minS = 1;
        if (isset($options['doNotCrop'])) {
            $doNotCrop = $options['doNotCrop'];
        }
        if (isset($options['minQuality'])) {
            $minQ = $options['minQuality'];
        }
        if (isset($options['minShortestSide'])) {
            $minS = $options['minShortestSide'];
        }

        if (!$doNotCrop) {
            $cropped = imagecropauto($img, IMG_CROP_DEFAULT);
            if ($cropped !== false) { // in case a new image resource was returned
                imagedestroy($img);    // we destroy the original image
                $img = $cropped;       // and assign the cropped image to $img
            }
        }

        // is width or height smaller?
        // get x from (width * x) == (smaller side)
        // will stay constant (so we don't need to update it) since the aspect ratio isn't changed
        // find minimal multiplier to not undershoot min shorter side
        $minMul =  $minS / (imagesy($img) < imagesx($img) ? imagesy($img) : imagesx($img));

        // set quality to 95 / the minimum Quality
        $quality = 95; //
        if ($quality < $minQ) {
            $quality = $minQ;
        }

        // get current size
        $cSize = imgJpgSize($img,$quality);
        $cMul = 1;
        $t = false;

        while ($cSize > $maxFSize) {
            if ($t)
                return false;
            $cMul /= 2;
            if ($cMul <= $minMul) {
                $cMul = $minMul;
                $t = true;
            }
            $img = imagescale($img,imagesx($img)*$cMul);
            $cSize = imgJpgSize($img,$quality);
        }
    }

    // to JPEG string (by capturing output from imagejpeg())
    ob_start();
    imagejpeg( $img, NULL, $quality );
    imagedestroy( $img ); // memory cleanup asap
    return ob_get_clean(); // clears memory and gives back output stream content
}

function imgJpgSize(resource $img, int $quality):int {
    ob_start();              // start the buffer
    imagejpeg($img, NULL, $quality);         // output image to buffer
    $size = ob_get_length(); // get size of buffer (in bytes)
    ob_end_clean();          // trash the buffer
    return $size;
}

/**
 * @param string $user
 * @param int $page
 * @param int $pageSize
 * @return array|false
 * @throws Exception
 */
function getTargetPageByUser(string $user, int $page = 0, int $pageSize = 5)
{
    if (empty($user))
        throw new Exception('Please Log in to view your targets!');
    $sql = <<<'SQL'
SELECT t.t_id, t.t_owner, t.xpos, t.ypos, t.map, t.content
FROM udvide.Targets t
LEFT JOIN Editors e
ON t.t_id = e.t_id
WHERE e.username = ?
ORDER BY t_id
LIMIT ?
OFFSET ?
SQL;
    $db = access_DB::prepareExecuteFetchStatement($sql, [$user, $pageSize, $page*$pageSize]);
    if ($db === false)
        return false; // no targets for $user
    $result = [];
    foreach ($db as $value) {
        $vw = json_decode((new access_vfc())
            ->setAccessMethod('summary')
            ->setTargetId($value['t_id'])
            ->execute()
            ->getBody());
        logTransaction($vw->transaction_id,$value['t_id']);

        $value['t_name'] = $vw->target_name;
        $value['active'] = $vw->active_flag;
        $value['database'] = $vw->database_name;
        $value['track_rating'] = $vw->tracking_rating;
        $value['upl_date'] = $vw->upload_date;
        $value['recos_total'] = $vw->total_recos;
        $value['recos_this_month'] = $vw->current_month_recos;
        $value['recos_last_month'] = $vw->previous_month_recos;
        $result[] = $value;
    }
    return $result;
}

/**
 * @param $tr_id
 * @param $t_id
 */
function logTransaction($tr_id, $t_id = 'no specific')
{
    $sql = "INSERT INTO udvide.TransactionLog VALUES ($tr_id,?,$t_id)";
    access_DB::prepareExecuteFetchStatement($sql, [$this->postData['username']]);
}

define('PERMISSIONS_ROOT',4);
define('PERMISSIONS_DEVELOPER',3);
define('PERMISSIONS_CLIENT',2);
define('PERMISSIONS_ADMIN',1);
define('PERMISSIONS_EDITOR',0);
/**
 * @param string $new_users_name
 * @param string $new_users_password
 * @param int $new_users_role
 * @param string $username the username of the submitting person
 * @param string $password the password of the submitting person
 * @throws Exception
 */
function addUser(string $new_users_name,string $new_users_password, int $new_users_role, string $username, string $password) {
    // Everyone can create accounts only below their permissions -> we make a root with perm = 4
    $perm = getPermissions($username, $password)[0];
    if ($new_users_role >= $perm) {
        throw new Exception("Insufficient Permissions! you $username have permission level $perm");
    }
    $keys = json_decode(file_get_contents('keys.json'));
    $new_users_password = password_hash(sha1($new_users_password . $keys->pepper),PASSWORD_DEFAULT);
    $sql= 'INSERT INTO udvide.Users VALUES (?,?,?)';
    access_DB::prepareExecuteFetchStatement($sql,[$new_users_name,$new_users_password,$new_users_role]);
}
