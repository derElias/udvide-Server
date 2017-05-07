<?php
require_once '../../vuforiaaccess.php';
// See the Vuforia Web Services Developer API Specification - https://developer.vuforia.com/resources/dev-guide/retrieving-target-cloud-database
// The GetTarget sample demonstrates how to query a single target by target id.
class GetSummary implements VuFoWorker {
	
	//Server Keys
	private $access_key;
	private $secret_key;
	
	private $targetId;
	private $url;
	private $requestPath;
	private $request;

    public function __construct()
    {
        $this->access_key   = vuforiaaccess::getAccessKey();
        $this->secret_key   = vuforiaaccess::getSecretKey();
        $this->url          = vuforiaaccess::getUrl();
        $this->requestPath  = vuforiaaccess::getTargetSummaryPath();
    }
	
	public function execute() {

        $this->requestPath = "$this->requestPath/$this->targetId";

		return $this->execGetSummary();
	}

    public function validateData()
    {
        if (!empty($this->targetId))
            trigger_error("no target ID set - invalid SUMMARY Request\nTo get a list of TargetIDs try SUMMARYALL",E_USER_ERROR);
        return $this;
    }
	
	private function execGetSummary(){
		
		$this->request = new HTTP_Request2();
		$this->request->setMethod( HTTP_Request2::METHOD_GET );
		
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
		// Generate the Auth field value by concatenating the public server access key w/ the private query signature for this request
		$this->request->setHeader("Authorization" , "VWS " . $this->access_key . ":" . $sb->tmsSignature( $this->request , $this->secret_key ));
	}

    /**
     * @param mixed $targetId
     * @return GetSummary
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }
}