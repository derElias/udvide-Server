<?php
require_once '../../vuforiaaccess.php';

// See the Vuforia Web Services Developer API Specification - https://developer.vuforia.com/resources/dev-guide/retrieving-target-cloud-database
// The DeleteTarget sample demonstrates how to delete a target from its Cloud Database using the target's target id.
// * note that targets cannot be 'Processing' and must be inactive to be deleted.

class DeleteTarget implements VuFoWorker {

	//Server Keys
	private $access_key;
	private $secret_key;

	private $url;
	private $requestPath;

    private $targetId;

	private $request; // internal

    public function __construct()
    {
        $this->access_key   = vuforiaaccess::getAccessKey();
        $this->secret_key   = vuforiaaccess::getSecretKey();
        $this->url          = vuforiaaccess::getUrl();
        $this->requestPath  = vuforiaaccess::getRequestPath();
    }

    /**
     * @param string $targetId
     * @return DeleteTarget
     */
    public function setTargetId(string $targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }

    public function execute()
    {
        $this->requestPath = "$this->requestPath/$this->targetId";
        return $this->execDeleteTarget();
    }

    public function validateData()
    {
        if (!empty($this->targetId))
            trigger_error('no target ID set - invalid DELETE Request',E_USER_ERROR);
        return $this;
    }

	private function execDeleteTarget(){

		$this->request = new HTTP_Request2();
		$this->request->setMethod( HTTP_Request2::METHOD_DELETE );
		
		$this->request->setConfig(array(
				'ssl_verify_peer' => false
		));
		$this->request->setURL( $this->url . $this->requestPath );

		// Define the Date and Authentication headers
		$this->setHeaders();

		try {
			$response = $this->request->send();

			if (200 == $response->getStatus()) {
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
		// Generate the Auth field value by concatenating the public server access key w/ the private query signature for this request
		$this->request->setHeader("Authorization" , "VWS " . $this->access_key . ":" . $sb->tmsSignature( $this->request , $this->secret_key ));
	}
}