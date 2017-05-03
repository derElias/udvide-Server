<?php
require_once 'vufoenviroment.php';

/**
 * Class PostNewTarget
 * Heavily based on Vuforia Samples this loads
 */
class PostNewTarget{

	//Server Keys
	private $access_key 	= "[error at vufoenviroment/PostNewTarget]";
	private $secret_key 	= "[error at vufoenviroment/PostNewTarget]";
	
	//private $targetId 		= "eda03583982f41cdbe9ca7f50734b9a1";
	private $url 			= "https://vws.vuforia.com";
	private $requestPath 	= "/targets";

	/**
     * @var HTTP_Request2
     */
	private $request;
	private $jsonRequestObject;
	
	private $targetName 	= "[error at vufoenviroment/PostNewTarget]";
	private $image       	= "[error at vufoenviroment/PostNewTarget]";
	private $width			= "[error at vufoenviroment/PostNewTarget]";
	private $meta			= "[error at vufoenviroment/PostNewTarget]";
	private $activeflag		= "[error at vufoenviroment/PostNewTarget]";

	function __construct()
    {
    	$this->access_key   = vufoenviroment::getAccessKey();
    	$this->secret_key   = vufoenviroment::getSecretKey();
    }

    public function setName($name) {
	    if ($name != false)
	        $this->targetName   = $name;
	    return $this;
    }

    public function setImage($image) {
        if ($image != false)
	        $this->image   = $image;
        return $this;
    }

    public function setWidth($width) {
        if ($width != false)
	        $this->width   = $width;
        return $this;
    }

    public function setMeta($meta) {
        if ($meta != false)
	        $this->meta = $meta;
        return $this;
    }

    public function setActiveflag($activeflag) {
        if ($activeflag != false)
	        $this->activeflag = $activeflag;
        return $this;
    }

    public function PostNewTarget()
	{
		$send = array(
			'width'                 =>  $this->width,
			'name'                  =>  $this->targetName,
			'image'                 =>  $this->getImageAsBase64(),
			'application_metadata'  =>  base64_encode($this->meta),
			'active_flag'           =>  $this->activeflag );
		$this->jsonRequestObject = json_encode( $send );
		return $this->execPostNewTarget();
	}
	
	function getImageAsBase64()
	{
		$file = $this->image;
		return $file ? base64_encode( $file ) : $file;
	}

	private function execPostNewTarget()
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
				return 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
						$response->getReasonPhrase(). ' ' . $response->getBody();
			}
		} catch (HTTP_Request2_Exception $e) {
			return 'Error: ' . $e->getMessage();
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
?>