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
    $keys = json_decode(file_get_contents('../keys.json'));
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
JOIN Editors e
ON u.username = e.username
WHERE u.username = ?
SQL;
    $db = access_DB::prepareExecuteGetStatement($sql, [$user]); // Documentation: this is how to follow Don't trust the user with dbaccess in addition to purify
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
 * @param int $quality accepts 1-100; defines JPEG compression quality (100% == best quality)
 * @return string
 */
function toJPG(string $imgString,int $quality = 100):string
{
    return jpgAssistant($imgString,['quality'=>$quality]);
}

/**
 * @param string $imgString accepts gd2, gd2part, gd, gif, png, wbmp, webp, xbm and xpm. bmp supported if php 7.2.0+ is used
 * @param int $size
 * @return string
 */
function compressToJpg(string $imgString, int $size):string
{
    return jpgAssistant($imgString,['maxFileSize'=>$size]);
}

/**
 * @param string $imgString
 * @param array $options
 * @return string
 */
function jpgAssistant (string $imgString, array $options):string {
    // to image instance
    $img = imagecreatefromstring($imgString);

    // get options
    $quality = 100;
    $minQ = 5;
    $maxFSize = PHP_INT_MAX; // Should equal 2GB
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

        // get current size
        $cSize = imgJpgSize($img,$quality);
        $cMul = 1;
        while ($cSize > $maxFSize) {
            $quality = $maxFSize / $cSize;
            $quality = (int) $quality-1;

            if ($quality < $minQ) {
                $quality = $minQ;
            }


            $cSize = imgJpgSize($img,$quality);
        }
    }

    // to JPEG stream (by capturing output from imagejpeg())
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
