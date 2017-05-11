<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.05.2017
 * Time: 19:53
 *
 * temporary class
 * task trying to accomplish: refactoring vuforiaaccess
 */
class vfcAccess
{
    private $url = "https://vws.vuforia.com";
    private $targetRequestPath = "/targets";
    private $targetSummaryPath = "/summary";

    private $accessmethod;

    private $targetId;
    private $targetName;
    private $image;
    private $width;
    private $meta;
    private $activeflag;

    private $access_key;
    private $secret_key;

    /**
     * vfcAccess constructor.
     * reads VWS keys from keys.json in parent dictionary
     */
    public function __construct()
    {
        $keys = json_decode(file_get_contents('../keys.json'));
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
    }

    //<editor-fold desc="Fluent Setters /w validation">
    /**
     * Trying to let the client do as much as possible and correcting as much potential Errors as possible
     */

    /**
     * @param string $accessmethod
     * @return vfcAccess
     */
    public function setAccessmethod($accessmethod): vfcAccess
    {
    ['C', 'CREATE', 'POST'];
                ['R', 'READ', 'GET'];
        ['RA', 'READALL', 'GETALL'];
        ['U', 'UPD', 'PUT', 'UPDATE'];
        ['D', 'DEL', 'DELETE'];
        ['S', 'SUM', 'SUMMARIZE', 'SUMMARY'];
        ['SA', 'SUMALL'];/*
            case 'SUMMARIZEALL':
            case 'SUMMARYALL':
                $response = $this->callSummaryAll();
                break;
            default:
                trigger_error("INVALID VUFORIAACCESS OPERATION!\n
                Got $this->accessmethod instead of POST, GET, GETALL, UPDATE, UPD, DELETE, DEL,\n
                SUM, SUMMARIZE, SUMMARY, SUMALL, SUMMARIZEALL, SUMMARYALL!",E_USER_ERROR);
                $response = 'trigger_error seems to not work properly...'; // Should be unreachable code
                break;*/
        $this->accessmethod = $accessmethod;
        return $this;
    }

    /**
     * @param string $targetId
     * @return vfcAccess
     * @throws VuforiaAccessAPIException
     */
    public function setTargetId(string $targetId): vfcAccess
    {
        if (strlen($targetId) != 32)
            throw new VuforiaAccessAPIException('TargetID invalid (length != 32)');
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * @param string $targetName
     * @return vfcAccess
     * @throws VuforiaAccessAPIException
     */
    public function setTargetName(string $targetName): vfcAccess
    {
        if ($targetName === '')
            throw new VuforiaAccessAPIException('Recoverable Error: TargetName empty', 1);
        if (strlen($this->targetName) > 64)
            throw new VuforiaAccessAPIException('Recoverable Error: targetName longer then 64 characters', 1);
        $this->targetName = $targetName;
        return $this;
    }

    /**
     * @param string $image // base 64 encoded
     * @return vfcAccess
     */
    public function setImage(string $image): vfcAccess
    {
//        $image.image_type_to_extension()
        $this->image = $image;
        return $this;
    }

    /**
     * @param string $imagePath
     * @return vfcAccess
     */
    public function setImageByPath(string $imagePath): vfcAccess
    {
        $this->image = file_get_contents($imagePath);
        return $this;
    }

    /**
     * @param float $width
     * @return vfcAccess
     */
    public function setWidth(float $width): vfcAccess
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param string $meta
     * @return vfcAccess
     */
    public function setMeta(string $meta): vfcAccess
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param bool $activeflag
     * @return vfcAccess
     */
    public function setActiveflag(bool $activeflag): vfcAccess
    {
        $this->activeflag = $activeflag;
        return $this;
    }

    //</editor-fold>

    /**
     * @return HTTP_Request2_Response
     * Selects based on $accessMethod which call to send
     */
    public function execute():HTTP_Request2_Response
    {
        $this->accessmethod = strtoupper($this->accessmethod);
        switch ($this->accessmethod) {
            case 'C':
            case 'CREATE':
            case 'POST':
                $response = $this->callPost();
                break;
            case 'R':
            case 'READ':
            case 'GET':
                $response = $this->callGet();
                break;
            case 'RA':
            case 'READALL':
            case 'GETALL':
                $response = $this->callGetAll();
                break;
            case 'U':
            case 'UPD':
            case 'PUT':
            case 'UPDATE':
                $response = $this->callUpdate();
                break;
            case 'D':
            case 'DEL':
            case 'DELETE':
                $response = $this->callDelete();
                break;
            case 'S':
            case 'SUM':
            case 'SUMMARIZE':
            case 'SUMMARY':
                $response = $this->callSummary();
                break;
            case 'SA':
            case 'SUMALL':
            case 'SUMMARIZEALL':
            case 'SUMMARYALL':
                $response = $this->callSummaryAll();
                break;
            default:
                trigger_error("INVALID VUFORIAACCESS OPERATION!\n
                Got $this->accessmethod instead of POST, GET, GETALL, UPDATE, UPD, DELETE, DEL,\n
                SUM, SUMMARIZE, SUMMARY, SUMALL, SUMMARIZEALL, SUMMARYALL!",E_USER_ERROR);
                $response = 'trigger_error seems to not work properly...'; // Should be unreachable code
                break;
        }
        return $response;
    }

    //<editor-fold desc="Calls">

    /**
     * @return HTTP_Request2_Response
     */
    private function callPost(): HTTP_Request2_Response
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_POST );

        // build array to be sent as body
        $send = [];
        // required stuff
        $send['width'] = 500.0; // Docu: this is an arbitrary value that could be used to estimate real distances if we could influence the way the client wants his marker... could be expanded upon; decision against as it does not provide enough value; should be greater then nearclip-target distance according to https://developer.vuforia.com/users/davidbeard on https://developer.vuforia.com/forum/general-discussion/image-target-width
        $send['name'] = $this->targetName;
        // optional stuff
        if (!empty($this->image)) {
            $send['image'] = base64_encode($this->image);
        }
        if (!empty($this->meta)) {
            $send['application_metadata'] = base64_encode($this->meta);
        }
        if (!empty($this->activeflag)) {
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
        if (!empty($this->targetName)) {
            $send['name'] = $this->targetName;
        }
        if (!empty($this->image)) {
            $send['image'] = base64_encode($this->image);
        }
        if (!empty($this->meta)) {
            $send['application_metadata'] = base64_encode($this->meta);
        }
        if (!empty($this->activeflag)) {
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

        // Not all requests will define a content-type
        $hexDigest = 'd41d8cd98f00b204e9800998ecf8427e'; // Hex digest of empty
        $contentType = '';
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
