<?php
require_once '/xampp/php/pear/HTTP/Request2.php'; // change this line to the HTTP/Request2.php path e.g. /xampp/php/pear/... or /php/...

/**
 * Created by: Simon Janssen
 * Contains Licensed Code from Qualcomm Austria Research Center GmbH
 * Date: 10.05.2017
 * Time: 19:53
 */
class access_vfc
{
    private $url = "https://vws.vuforia.com";
    private $targetRequestPath = "/targets";
    private $targetSummaryPath = "/summary";

    private $accessMethod;

    private $targetId;
    private $targetName;
    private $image;
    private $meta;
    private $activeflag;

    private $access_key;
    private $secret_key;

    /**
     * vfcAccess constructor.
     * reads VWS keys from keys.json in root directory
     */
    public function __construct()
    {
        $keys = json_decode(file_get_contents('keys.json'));
        if ($keys === false)
            trigger_error('keys.json konnte nicht eingelesen werden!',E_USER_ERROR);
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
    }

    //<editor-fold desc="Fluent Setters /w validation">
    /**
     * Trying to let the client do as much as possible and
     * throwing recoverable Errors so the frontend can decide how to solve the problem based on the specific case
     */

    /**
     * @param string $targetId
     * @return access_vfc
     * @throws VuforiaAccessAPIException
     */
    public function setTargetId(string $targetId): access_vfc
    {
        if (strlen($targetId) != 32)
            throw new VuforiaAccessAPIException('TargetID invalid (length != 32)',200);
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * @param string $targetName
     * @return access_vfc
     * @throws VuforiaAccessAPIException
     */
    public function setTargetName(string $targetName): access_vfc
    {
        if ($targetName === '')
            throw new VuforiaAccessAPIException('Recoverable Error: TargetName empty', 110);
        if (strlen($this->targetName) > 64)
            throw new VuforiaAccessAPIException('Recoverable Error: targetName longer then 64 characters', 111);
        $this->targetName = $targetName;
        return $this;
    }

    /**
     * @param string $image
     * @return access_vfc
     * @throws VuforiaAccessAPIException
     */
    public function setImage(string $image): access_vfc
    {
        // Jpg is hinted and checked by FF D8 @start and FF D9 @end as magic numbers
        // Docu: $image[-x] requires PHP 7.1+; but doesn'T seem to work in test env. using classic Syntax instead
        // Validation method chosen to get a compromise between performance (could've checked file endings) and accuracy (use internal function)
        $isJpg = (ord($image{0}) == 255)
            && (ord($image{1}) == 216)
            && (ord($image[strlen($image)-2]) == 255)
            && (ord($image[strlen($image)-1]) == 217);
        if ($isJpg) {
            $this->image = $image;
        } else {
            throw new VuforiaAccessAPIException('Recoverable Error: Image set to non JPEG / JPG file', 120);
        }
        return $this;
    }

    /**
     * @param string $imagePath
     * @return access_vfc
     */
    public function setImageByPath(string $imagePath): access_vfc
    {
        return $this->setImage(file_get_contents($imagePath));
    }

    /**
     * @param string $meta
     * @return access_vfc
     * @throws VuforiaAccessAPIException
     */
    public function setMeta(string $meta): access_vfc
    {
        // Docu: Apparently Vuforia only accepts a MetaData of less than 2mb,
        // and we'll give  a bit of tolerance to reduce required testing time of the VWS
        if (strlen($meta)<2000000) {
            $this->meta = $meta;
        } else {
            throw new VuforiaAccessAPIException('Human Interaction required - Error: The Meta you\'re trying to set is larger than 2mb',230);
        }
        return $this;
    }

    /**
     * @param bool $activeflag
     * @return access_vfc
     */
    public function setActiveflag(bool $activeflag): access_vfc
    {
        $this->activeflag = $activeflag;
        return $this;
    }

    /**
     * @param string $accessMethod
     * @return access_vfc
     */
    public function setAccessMethod($accessMethod)
    {
        $this->accessMethod = strtolower($accessMethod);
        return $this;
    }
    //</editor-fold>

    /**
     * Selects based on $accessMethod which call to send
     * @return HTTP_Request2_Response
     * @throws VuforiaAccessAPIException
     */
    public function execute():HTTP_Request2_Response
    {
        switch ($this->accessMethod) {
            case 'create':
                $response = $this->callPost();
                break;
            case 'get':
                $response = $this->callGet();
                break;
            case 'getall':
                $response = $this->callGetAll();
                break;
            case 'update':
                $response = $this->callUpdate();
                break;
            case 'delete':
                $response = $this->callDelete();
                break;
            case 'summarize':
                $response = $this->callSummary();
                break;
            case 'summarizeall':
                $response = $this->callSummaryAll();
                break;
            default:
                throw new VuforiaAccessAPIException("UserError: INVALID VUFORIAACCESS OPERATION!\n
                    Got $this->accessMethod instead of post, get, getAll, update, delete, summarize, summarizeAll!", 251);
        }
        return $response;
    }

    //<editor-fold desc="Calls">

    /**
     * @return HTTP_Request2_Response
     * @throws VuforiaAccessAPIException
     */
    private function callPost(): HTTP_Request2_Response
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_POST );

        // build array to be sent as body
        $send = [];
        // required stuff
        $send['width'] = 500.0; // Docu: this is an arbitrary value that could be used to estimate real distances if we could influence the way the client wants his marker... could be expanded upon; decision against as it does not provide enough value; should be greater then nearclip-target distance according to https://developer.vuforia.com/users/davidbeard on https://developer.vuforia.com/forum/general-discussion/image-target-width
        if (isset($this->targetName)) {
            $send['name'] = $this->targetName;
        } else {
            throw new VuforiaAccessAPIException('Human Interaction required - Error: target name required to post',110);
        }
        // optional stuff
        if (isset($this->image)) {
            $send['image'] = base64_encode($this->image);
        }
        if (isset($this->meta)) {
            $send['application_metadata'] = base64_encode($this->meta);
        }
        if (isset($this->activeflag)) {
            $send['active_flag'] = $this->activeflag ? 1 : 0;
        }
        $request->setBody(json_encode($send));

        $request->setURL( $this->url . $this->targetRequestPath );

        $request->setHeader("Content-Type", "application/json");

        return $this->setCommonValuesAndSend($request); // un-clutter
    }

    /**
     * @return HTTP_Request2_Response
     */
    private function callGet():HTTP_Request2_Response
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_GET );

        $request->setURL( $this->url . $this->targetRequestPath . '/' . $this->targetId );

        return $this->setCommonValuesAndSend($request); // un-clutter
    }

    /**
     * @return HTTP_Request2_Response
     */
    private function callGetAll():HTTP_Request2_Response
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_GET );

        $request->setURL( $this->url . $this->targetRequestPath );

        return $this->setCommonValuesAndSend($request); // un-clutter
    }

    /**
     * @return HTTP_Request2_Response
     */
    private function callUpdate():HTTP_Request2_Response
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_PUT );

        // build array to be sent as body
        $send = [];
        // $send['width'] = 500.0;
        if (isset($this->targetName)) {
            $send['name'] = $this->targetName;
        }
        if (isset($this->image)) {
            $send['image'] = base64_encode($this->image);
        }
        if (isset($this->meta)) {
            $send['application_metadata'] = base64_encode($this->meta);
        }
        if (isset($this->activeflag)) {
            $send['active_flag'] = $this->activeflag ? 1 : 0;
        }
        $request->setBody(json_encode($send));

        $request->setURL( $this->url . $this->targetRequestPath . '/' . $this->targetId );

        $request->setHeader("Content-Type", "application/json");

        return $this->setCommonValuesAndSend($request); // un-clutter
    }

    /**
     * @return HTTP_Request2_Response
     */
    private function callDelete():HTTP_Request2_Response
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_DELETE );

        $request->setURL( $this->url . $this->targetRequestPath . '/' . $this->targetId );

        return $this->setCommonValuesAndSend($request); // un-clutter
    }

    /**
     * @return HTTP_Request2_Response
     */
    private function callSummary():HTTP_Request2_Response
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_GET );

        $request->setURL( $this->url . $this->targetSummaryPath . '/' . $this->targetId );

        return $this->setCommonValuesAndSend($request); // un-clutter
    }

    /**
     * @return HTTP_Request2_Response
     */
    private function callSummaryAll():HTTP_Request2_Response
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_GET );

        $request->setURL( $this->url . $this->targetSummaryPath );

        return $this->setCommonValuesAndSend($request); // un-clutter
    }

    //</editor-fold>

    /**
     * @param HTTP_Request2 $request
     * @param string $secret_key
     * @return string
     * Adapted from Vuforia Sample; All Rights reserved there
     */
    private function buildTmsSignature(HTTP_Request2 $request , string $secret_key ){

        $method = $request->getMethod();
        // The HTTP Header fields are used to authenticate the request
        $requestHeaders = $request->getHeaders();
        // note that header names are converted to lower case
        $dateValue = $requestHeaders['date'];

        $requestPath = $request->getURL()->getPath(); // Docu weird Behavior from phpstorm regarding installed packages

        $hexDigest = 'd41d8cd98f00b204e9800998ecf8427e'; // Hex digest of empty
        $contentType = '';
        // Not all requests will define a content-type
        if( isset( $requestHeaders['content-type'] ))
            $contentType = $requestHeaders['content-type'];

        if ( $method == 'GET' || $method == 'DELETE' ) {
            // Do nothing because the strings are already set correctly
        } else if ( $method == 'POST' || $method == 'PUT' ) {
            // If this is a POST or PUT the request should have a request body
            $hexDigest = md5( $request->getBody() , false );
        } else {
            print("ERROR: Invalid content type passed to Sig Builder");
        }
        $toDigest = $method . "\n" . $hexDigest . "\n" . $contentType . "\n" . $dateValue . "\n" . $requestPath ;
        $shaHashed = "";
        try {
            // the SHA1 hash needs to be transformed from hexadecimal to Base64
            $hex = hash_hmac("sha1", $toDigest , $secret_key);
            $decConcat = "";
            foreach(str_split($hex, 2) as $pair){
                $decConcat .= chr(hexdec($pair));
            }
            $shaHashed =  base64_encode($decConcat);
        } catch ( Exception $e) {
            $e->getMessage();
        }
        return $shaHashed;
    }

    /**
     * @param HTTP_Request2 $request
     * @return HTTP_Request2_Response
     * sets common values every VWS-API call requires
     * and Sends the request off
     */
    private function setCommonValuesAndSend(HTTP_Request2 $request): HTTP_Request2_Response
    {
        $date = new DateTime("now", new DateTimeZone("GMT"));

        $request->setConfig([
            'ssl_verify_peer' => false
        ]);

        // Adapted from Vuforia Sample; All Rights reserved there
        // Define the Date field using the proper GMT format
        $request->setHeader('Date', $date->format("D, d M Y H:i:s") . " GMT" );
        // Generate the Auth field value by concatenating the public server access key w/ the private query signature for this request
        $request->setHeader("Authorization" , "VWS " . $this->access_key . ":" . $this->buildTmsSignature( $request , $this->secret_key ));

        try {
            return $request->send();
        } catch (HTTP_Request2_Exception $e) { // Docu weird Behavior from phpstorm regarding installed packages
            trigger_error('Error: ' . $e->getMessage(),E_USER_ERROR);
        }
        return null;
    }
}
class VuforiaAccessAPIException extends Exception {}
