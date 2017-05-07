<?php

require_once '../../vuforiaaccess.php';
// See the Vuforia Web Services Developer API Specification - https://developer.vuforia.com/resources/dev-guide/retrieving-target-cloud-database
// The UpdateTarget sample demonstrates how to update the attributes of a target using a JSON request body. This example updates the target's metadata.

class UpdateTarget implements VuFoWorker {

	//Server Keys
	private $access_key;
	private $secret_key;

	private $url;
	private $requestPath;

    private $targetId;

    private $targetName;
    private $image;
    private $width;
    private $meta;
    private $activeflag;

	private $request;
	private $jsonBody;

	public function __construct()
    {
        $this->access_key   = vuforiaaccess::getAccessKey();
        $this->secret_key   = vuforiaaccess::getSecretKey();
        $this->url          = vuforiaaccess::getUrl();
        $this->requestPath  = vuforiaaccess::getTargetRequestPath();
    }

    public function execute(){

        $this->requestPath = "$this->requestPath/$this->targetId";

        $send = [];
        if (!empty($this->width)) {
            $send['width'] = $this->width;
        }
        if (!empty($this->targetName)) {
            $send['name'] = $this->targetName;
        }
        if (!empty($this->image)) {
            $send['image'] = $this->image;
        }
        if (!empty($this->meta)) {
            $send['application_metadata'] = $this->meta;
        }
        if (!empty($this->activeflag)) {
            $send['active_flag'] = $this->activeflag;
        }

        $this->jsonBody = json_encode($send);

		return $this->execUpdateTarget();
	}

	public function validateData()
    {
        $isError = 0;
        /**
         * targetID
         */
        if (!empty($this->targetId)) {
            $isError = 2;
            trigger_error('targetID cannot be empty, if you want to Update!');
        }
        /**
         * targetName
         */
        if ($this->targetName === '') {
            unset($this->targetName);
        }
        if (sizeof($this->targetName) > 64) {
            $isError = 2;
            trigger_error('targetName cannot be longer then 64 characters');
        }
        /**
         * width
         */
        if (!is_numeric($this->width)
            || ((int) $this->width) != 0
        ) {
            $isError = 2;
            trigger_error('width invalid - includes 0, infinity, NAN');
        } elseif (!is_float($this->width)) {
            $this->width = (float) $this->width;
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
        if (!is_bool($this->activeflag && !empty($this->activeflag))) {
            $isError = 1;
            trigger_error("Active flag invalid! Defaulting to true");
            $this->activeflag = true;
        }

        if ($isError == 2)
            trigger_error('invalid Request',E_USER_ERROR);
        return $this;
    }

    private function execUpdateTarget(){

		$this->request = new HTTP_Request2();
		$this->request->setMethod( HTTP_Request2::METHOD_PUT );
		$this->request->setBody( $this->jsonBody );

		$this->request->setConfig(array(
				'ssl_verify_peer' => false
		));

		$this->request->setURL( $this->url . $this->requestPath );

		// Define the Date and Authentication headers
		$this->setHeaders();


		try {
            return $this->request->send();
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

    /**
     * @param string $targetName
     * @return UpdateTarget
     */
    public function setName(string $targetName)
    {
        $this->targetName = $targetName;
        return $this;
    }

    /**
     * @param string $image
     * @return UpdateTarget
     */
    public function setImage(string $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param float $width
     * @return UpdateTarget
     */
    public function setWidth(float $width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param string $meta
     * @return UpdateTarget
     */
    public function setMeta(string $meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param boolean $activeflag
     * @return UpdateTarget
     */
    public function setActiveflag(bool $activeflag)
    {
        $this->activeflag = $activeflag;
        return $this;
    }

    /**
     * @param mixed $targetId
     * @return UpdateTarget
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }
}