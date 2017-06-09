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
 * @return string|false (smaller) JPG | false on failure
 */
function jpgAssistant ($img, array $options):string {
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
    $ret = imgResToJpgString($img,$quality);
    imagedestroy( $img ); // memory cleanup asap
    return $ret;
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
 * Convert an assoc. array to a target object
 * @param array $array_in associative array with opt: activeFlag map yPos xPos username t_image t_name t_id content
 * @return target
 */
function arrayToTarget(array $array_in):target
{
    $response = new target();
    if (isset($array_in['activeFlag'])) {
        $response->active = $array_in['activeFlag'];
    }
    if (isset($array_in['map'])) {
        $response->map = $array_in['map'];
    }
    if (isset($array_in['yPos'])) {
        $response->yPos = $array_in['yPos'];
    }
    if (isset($array_in['xPos'])) {
        $response->xPos = $array_in['xPos'];
    }
    if (isset($array_in['username'])) {
        $response->owner = $array_in['username'];
    }
    if (isset($array_in['t_image'])) {
        $response->image = $array_in['t_image'];
    }
    if (isset($array_in['t_name'])) {
        $response->name = $array_in['t_name'];
    }
    if (isset($array_in['t_id'])) {
        $response->id = $array_in['t_id'];
    }
    if (isset($array_in['content'])) {
        $response->content = $array_in['content'];
    }
    return $response;
}

/**
 * Represents a target with all it's keys
 * Class target
 */
class target {
    /** @var  string */
    public $name;
    /** @var  bool */
    public $deleted;
    /** @var  string|resource */
    public $image;
    /** @var  bool */
    public $active;
    /** @var  string */
    public $content;
    /** @var  string */
    public $owner;
    /** @var  int */
    public $xPos;
    /** @var  int */
    public $yPos;
    /** @var  string */
    public $map;
    /** @var  int */
    public $id;
    /** @var  string */
    public $vw_id;

    public function __construct() {}

    public static function fromJSON($json = false) {
        $instance = new self();
        if ($json) $instance->set(json_decode($json, true));
        return $instance;
    }

    public function set($data) {
        var_dump($data);
        foreach ($data AS $key => $value) {
            /*// snippet left from sample for object to custom object conversion
            if (is_array($value)) {
                $sub = new self();
                $sub->set($value);
                $value = $sub;
            }//*/
            $this->{$key} = $value;
        }
    }

    //<editor-fold desc="Fluent Setters (set null if omitted param except $deleted)">
    /**
     * @param string $name
     * @return target
     */
    public function setName(string $name = null): target
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param resource|string $image
     * @return target
     */
    public function setImage($image = null)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param bool $active
     * @return target
     */
    public function setActive(bool $active = null): target
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @param string $content
     * @return target
     */
    public function setContent(string $content = null): target
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param string $owner
     * @return target
     */
    public function setOwner(string $owner = null): target
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @param int $xPos
     * @return target
     */
    public function setXPos(int $xPos = null): target
    {
        $this->xPos = $xPos;
        return $this;
    }

    /**
     * @param int $yPos
     * @return target
     */
    public function setYPos(int $yPos = null): target
    {
        $this->yPos = $yPos;
        return $this;
    }

    /**
     * @param string $map
     * @return target
     */
    public function setMap(string $map = null): target
    {
        $this->map = $map;
        return $this;
    }

    /**
     * @param string $id
     * @return target
     */
    public function setId(string $id = null): target
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $vw_id
     * @return target
     */
    public function setVwId(string $vw_id = null): target
    {
        $this->vw_id = $vw_id;
        return $this;
    }

    /**
     * @param bool $deleted
     * @return target
     */
    public function setDeleted(bool $deleted = false): target
    {
        $this->deleted = $deleted;
        return $this;
    }
    //</editor-fold>
}

class PermissionException extends Exception {}

class handlerResponse {
    /** @var  bool */
    public $success;
    /** @var  string */
    public $message;
    /** @var  int */
    public $t_id;
}
