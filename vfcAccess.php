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

    /**
     * @return HTTP_Request2_Response
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
    private function callPost()
    {
        $request = new HTTP_Request2();
        $request->setMethod( HTTP_Request2::METHOD_POST );

        // build array to be sent as body
        $send = [];
        // required stuff
        $send['width'] = $this->width;
        $send['name'] = $this->targetName;

        // optional stuff
        if (!empty($this->image)) {
            $send['image'] = base64_encode($this->image);
        }
        if (!empty($this->meta)) {
            $send['application_metadata'] = base64_encode($this->meta);
        }
        if (!empty($this->activeflag)) {
            if (is_bool($this->activeflag)) {
                $send['active_flag'] = $this->activeflag ? 1 : 0;
            } elseif ($this->activeflag < 2 && $this->activeflag > 0) {
                $send['active_flag'] = $this->activeflag;
            } else {
                trigger_error('activeflag invalid; using default');
            }
        }
        $request->setBody(json_encode($send));

        $request->setURL( $this->url . $this->targetRequestPath );

        $request->setHeader("Content-Type", "application/json");

        $request = $this->setCommonValues($request); // un-clutter

        try {
            return $request->send();
        } catch (HTTP_Request2_Exception $e) { // Docu weird Behavior from phpstorm regarding installed packages
            trigger_error('Error: ' . $e->getMessage(),E_USER_ERROR);
        }
        return null;
    }
/*
    private function callGet()
    {
        return (new GetTarget($keys))
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callGetAll()
    {
        return (new GetAllTargets($keys))
            ->validateData()
            ->execute();
    }

    private function callUpdate()
    {
        $subject = new UpdateTarget($keys);
        if (!empty($this->targetName)) {
            $subject->setName($this->targetName);
        }
        if (!empty($this->image)) {
            $subject->setImage($this->image);
        }
        if (!empty($this->width)) {
            $subject->setWidth($this->width);
        }
        if (!empty($this->meta)) {
            $subject->setMeta($this->meta);
        }
        if (!empty($this->activeflag)) {
            $subject->setActiveflag($this->activeflag);
        }
        return $subject
            ->validateData()
            ->execute();
    }

    private function callDelete()
    {
        return (new DeleteTarget($keys))
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callSummary()
    {
        return (new GetSummary($keys))
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callSummaryAll()
    {
        return (new GetAllSummaries($keys))
            ->validateData()
            ->execute();
    }
*/
    //</editor-fold>

    //<editor-fold desc="Fluent Setters /w validation"> // ToDo

    /**
     * @param string $accessmethod
     * @return vfcAccess
     */
    public function setAccessmethod($accessmethod): vfcAccess
    {
        $this->accessmethod = $accessmethod;
        return $this;
    }

    /**
     * @param string $targetId
     * @return vfcAccess
     */
    public function setTargetId(string $targetId): vfcAccess
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * @param string $targetName
     * @return vfcAccess
     */
    public function setTargetName(string $targetName): vfcAccess
    {
        $this->targetName = $targetName;
        return $this;
    }

    /**
     * @param string $image // base 64 encoded
     * @return vfcAccess
     */
    public function setImage(string $image): vfcAccess
    {
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
        $hexDigest = '';
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
     * @return HTTP_Request2
     * sets common values every VWS-API call requires
     */
    private function setCommonValues(HTTP_Request2 $request): HTTP_Request2
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

        return $request;
    }
}
