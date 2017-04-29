<?php

require_once 'HTTP/Request2.php';
require_once 'SignatureBuilder.php';

// See the Vuforia Web Services Developer API Specification - https://developer.vuforia.com/resources/dev-guide/retrieving-target-cloud-database
// The PostNewTarget sample demonstrates how to update the attributes of a target using a JSON request body. This example updates the target's metadata.

class PostNewTarget{

	//Server Keys
	private $access_key 	= "[ server access key ]";
	private $secret_key 	= "[ server secret key ]";
	
	//private $targetId 		= "eda03583982f41cdbe9ca7f50734b9a1";
	private $url 			= "https://vws.vuforia.com";
	private $requestPath 	= "/targets";
	private $request;       // the HTTP_Request2 object
	private $jsonRequestObject;
	
	private $targetName 	= "[ name ]";
	private $imageLocation 	= "[ /path/file.ext ]";
	
	function PostNewTarget(){
		
		$this->jsonRequestObject = json_encode( array( 'width'=>320.0 , 'name'=>$this->targetName , 'image'=>$this->getImageAsBase64() , 'application_metadata'=>base64_encode("Vuforia test metadata") , 'active_flag'=>1 ) );

		$this->execPostNewTarget();

	}
	
	function getImageAsBase64(){
		
		$file = file_get_contents( $this->imageLocation );
		
		if( $file ){
			
			$file = base64_encode( $file );
		}
		
		return $file;
	
	}

	public function execPostNewTarget(){

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
				echo $response->getBody();
			} else {
				echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
						$response->getReasonPhrase(). ' ' . $response->getBody();
			}
		} catch (HTTP_Request2_Exception $e) {
			echo 'Error: ' . $e->getMessage();
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
