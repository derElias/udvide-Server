<?php
require_once 'settings.php';
require_once 'access_DB.php';
require_once 'access_vfc.php';

/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.05.2017
 * Time: 16:12
 */

/**
 * Takes an GD image resource or an image string and converts it to a potentially smaller jpg string with given options
 * @param string|resource $img accepts gd2, gd2part, gd, gif, png, wbmp, webp, xbm and xpm. bmp supported if php 7.2.0+ is used
 * @param array $options supports quality, maxFileSize, doNotCrop, minQuality, minShortestSide
 * @return resource (smaller) JPG | false on failure
 */
function imgAssistant (&$img, array $options) {
    // to image resource if not already
    if (is_string($img))
        $img = imagecreatefromstring($img);

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

        if (imgJpgSize($img) < $maxFSize)
            return $img;

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
                return $img; // Image couldn't hit the requirements - returning approximation
            $cMul *= 0.8;
            if ($cMul <= $minMul) {
                $cMul = $minMul;
                $t = true;
            }
            $img = imagescale($img,imagesx($img)*$cMul);
            $cSize = imgJpgSize($img,$quality);
        }
    }

    // to JPEG string (by capturing output from imagejpeg())
    //$ret = imgResToJpgString($img,$quality);
    //imagedestroy( $img ); // memory cleanup asap

    return $img;
}

/**
 * Convert an image resource to an jpg string
 * @param resource $img
 * @param int $quality
 * @return string
 */
function imgResToJpgString($img, int $quality = 95) {
    ob_start();
    imagejpeg( $img, NULL, $quality );
    return ob_get_clean(); // clears memory and gives back output stream content
}

/**
 * Get the size a image resource would have as a jpg
 * @param resource $img
 * @param int $quality
 * @return int
 */
function imgJpgSize($img, int $quality = 95):int {
    ob_start();              // start the buffer
    imagejpeg($img, NULL, $quality);         // output image to buffer
    $size = ob_get_length(); // get size of buffer (in bytes)
    ob_end_clean();          // trash the buffer
    return $size;
}



/**
 * Log a tansaction into the database
 * @param string $tr_id
 * @param string $user
 * @param string $t_id
 */
function logTransaction($tr_id, $user, $t_id = "no specific")
{
    $sql = "INSERT INTO udvide.TransactionLog (tr_id, username, t_id) VALUES (?,?,?)";
    access_DB::prepareExecuteFetchStatement($sql, [$tr_id,$user,$t_id]);
}

/**
 * @param string $value
 * @return string
 */
function purifyValue(string $value):string {
    return htmlspecialchars(stripslashes(trim($value)));
}

/**
 * @param string $in
 * @return string
 * @throws TypeError
 */
function base64ImgToDecodeAbleBase64(string $in):string {
    $out = explode(',',$in);

    if (!array_key_exists(1,$out) || array_key_exists(2,$out))
        // A invalid base 64 img input was given
        throw new TypeError(ERR_TYPE_NOT_BASE64_IMAGE_MSG);

    return $out[1];
}

/**
 * Compares a sent in passHash (password) with a peppered and salted passHash
 * @param string $userPassHash the sent password value (should be hashed client-side)
 * @param string $serverPassHash the db stored password
 * @return bool
 */
function pepperedPassCheck(string $userPassHash,string $serverPassHash):bool
{
    $keys = json_decode(file_get_contents('keys.json'));
    return password_verify(sha1($userPassHash . $keys->pepper), $serverPassHash);
}

/**
 * generates a peppered and salted passHash
 * @param string $new_users_password
 * @return bool|string
 */
function pepperedPassGen(string $new_users_password)
{
    $keys = json_decode(file_get_contents('keys.json'));
    return password_hash(sha1($new_users_password . $keys->pepper), PASSWORD_DEFAULT);
}

/**
 * Based on JPGs magic numbers we can quickly figure out if a file is defiantly not a JPG
 * false-positives unlikely but possible
 * false-negatives impossible
 * @param string $img
 * @return bool
 */
function strIsJpg(string $img):bool {
    return (ord($img{0}) == 255)
    && (ord($img{1}) == 216)
    && (ord($img[strlen($img) - 2]) == 255)
    && (ord($img[strlen($img) - 1]) == 217);
}

function sanitizeXML($in) {

    $search = array(
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/' // Remove HTML comments
    );

    $replace = array(
        '>',
        '<',
        '\\1',
        ''
    );

    return preg_replace($search, $replace, $in);
}


class LoginException extends Exception {}
class PermissionException extends Exception {}
class IncompleteObjectException extends Exception {}

class handlerResponse {
    /** @var  bool */
    public $success;
    /** @var  string */
    public $message;
    /** @var  int */
    public $t_id;
}
