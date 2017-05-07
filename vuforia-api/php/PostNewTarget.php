<?php
require_once '../../vuforiaaccess.php';

/**
 * Class PostNewTarget
 * Heavily based on Vuforia Samples
 */
class PostNewTarget implements VuFoWorker {

	//Server Keys
	private $access_key;
	private $secret_key;
	private $url;
	private $requestPath;

	/**
     * @var HTTP_Request2
     */
	private $request;
	private $jsonRequestObject;

    //private $targetId 		= "eda03583982f41cdbe9ca7f50734b9a1";

	private $targetName;
	private $image;
	private $width;
	private $meta;
	private $activeflag;

	function __construct()
    {
    	$this->access_key   = vuforiaaccess::getAccessKey();
    	$this->secret_key   = vuforiaaccess::getSecretKey();
    	$this->url          = vuforiaaccess::getUrl();
    	$this->requestPath  = vuforiaaccess::getRequestPath();
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name) {
        $this->targetName = $name;
	    return $this;
    }

    /**
     * @param string $image
     * @return $this
     */
    public function setImage(string $image) {
        $this->image   = $image;
        return $this;
    }

    /**
     * @param float|integer $width
     * @return $this
     */
    public function setWidth($width) {
        $this->width   = $width;
        return $this;
    }

    /**
     * @param string $meta
     * @return $this
     */
    public function setMeta(string $meta) {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param bool $activeflag
     * @return $this
     */
    public function setActiveflag(bool $activeflag) {
        $this->activeflag = $activeflag;
        return $this;
    }

    public function execute()
	{
		$send = [];
		// required stuff
		$send['width'] = $this->width;
		$send['name'] = $this->targetName;

		// optional stuff
        if (!empty($this->image)) {
            $send['image'] = $this->image;
        }
        if (!empty($this->meta)) {
            $send['application_metadata'] = $this->meta;
        }
        if (!empty($this->activeflag)) {
            $send['active_flag'] = $this->activeflag;
        }

		$this->jsonRequestObject = json_encode( $send );
		return $this->execPostNewTarget();
	}

    /**
     * @return PostNewTarget
     * might convert an integer width to a float
     */
    public function validateData()
    {
        $isError = 0;
        /**
         * targetName
         */
        if (empty($this->targetName)
            || $this->targetName === '') {
            $isError = 2;
            trigger_error('targetName is required when you POST');
        }
        if (sizeof($this->targetName) > 64) {
            $isError = 2;
            trigger_error('targetName cannot be longer then 64 characters');
        }
        /**
         * width
         */
        if (empty($this->width)) {
            $isError = 2;
            trigger_error('width is required when you POST');
        }
        if (is_numeric($this->width)
            && (int) $this->width != 0) { // is width is infinity or zero or NAN or not numeric, this will get it
            if (!is_float($this->width))
                $this->width = (float)$this->width;
        } else {
            $isError = 2;
            trigger_error('width invalid - includes 0, infinity, NAN');
        }
        /**
         * image
         */
        if (base64_decode($this->image,true) === false && !empty($this->image)) {
            $isError = 1;
            trigger_error("Image seems invalid; recovering by re-encoding\nOriginal Image: $this->image");
            $this->image = base64_encode($this->image);
        }
        /**
         * activeflag
         */
        if (!is_bool($this->activeflag) && !empty($this->activeflag)) {
            $isError = 1;
            trigger_error("Active flag not set or invalid! Defaulting to true");
            $this->activeflag = true;
        }

        if ($isError == 2)
            trigger_error('invalid Request',E_USER_ERROR);
        return $this;
    }

	private function execPostNewTarget() // completly unmodified
	{
		$this->request = new HTTP_Request2();
		$this->request->setMethod( HTTP_Request2::METHOD_POST );
		$this->request->setBody( $this->jsonRequestObject );

		$this->request->setConfig(array(
				'ssl_verify_peer' => false
		));

		$this->request->setURL( $this->url . $this->requestPath );

		// Define the Date and Authentication headers
		$this->setHeaders();

		try {
			$response = $this->request->send();
			if (200 == $response->getStatus() || 201 == $response->getStatus() ) {
				return $response->getBody();
			} else {
				trigger_error('Unexpected HTTP status: ' . $response->getStatus() . ' ' .
						$response->getReasonPhrase(). ' ' . $response->getBody(),E_USER_ERROR);
			}
		} catch (HTTP_Request2_Exception $e) {
			trigger_error('Error: ' . $e->getMessage(),E_USER_ERROR);
		}
	}

	private function setHeaders(){
		$sb = 	new SignatureBuilder();
		$date = new DateTime("now", new DateTimeZone("GMT"));

		// Define the Date field using the proper GMT format
		$this->request->setHeader('Date', $date->format("D, d M Y H:i:s") . " GMT" );
		$this->request->setHeader("Content-Type", "application/json" );
		// Generate the Auth field value by concatenating the public server access key w/ the private query signature for this request
		$this->request->setHeader("Authorization" , "VWS " . $this->access_key . ":" . $sb->tmsSignature( $this->request , $this->secret_key ));
	}
}