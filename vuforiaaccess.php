<?php
require_once '/xampp/php/pear/HTTP/Request2.php'; // change this line to the HTTP/Request2.php path e.g. /xampp/php/pear/... or /php/...

class vuforiaaccess {
    private static $url = "https://vws.vuforia.com";
    private static $targetRequestPath = "/targets";
    private static $targetSummaryPath = "/summary";

    private $keys;

    private $accessmethod;

    private $targetId;
    private $targetName;
    private $image;
    private $width;
    private $meta;
    private $activeflag;

    //<editor-fold desc="static Getters">
    /**
     * @return string
     */
    public static function getTargetRequestPath(): string
    {
        return self::$targetRequestPath;
    }

    /**
     * @return string
     */
    public static function getUrl(): string
    {
        return self::$url;
    }

    /**
     * @return string
     */
    public static function getTargetSummaryPath(): string
    {
        return self::$targetSummaryPath;
    }
    //</editor-fold>

    public function __construct()
    {
        $this->keys = json_decode(file_get_contents('../keys.json'));
    }

    /**
     * @return mixed
     */
    public function execute() {

        $this->accessmethod = strtoupper($this->accessmethod);
        switch ($this->accessmethod) {
            case 'C':
            case 'CREATE':
            case 'POST':
                $response = $this->callPost($this->keys);
                break;
            case 'R':
            case 'READ':
            case 'GET':
                $response = $this->callGet($this->keys);
                break;
            case 'RA':
            case 'READALL':
            case 'GETALL':
                $response = $this->callGetAll($this->keys);
                break;
            case 'U':
            case 'UPD':
            case 'UPDATE':
                $response = $this->callUpdate($this->keys);
                break;
            case 'D':
            case 'DEL':
            case 'DELETE':
                $response = $this->callDelete($this->keys);
                break;
            case 'S':
            case 'SUM':
            case 'SUMMARIZE':
            case 'SUMMARY':
                $response = $this->callSummary($this->keys);
                break;
            case 'SA':
            case 'SUMALL':
            case 'SUMMARIZEALL':
            case 'SUMMARYALL':
                $response = $this->callSummaryAll($this->keys);
                 break;
            default:
                trigger_error("INVALID VUFORIAACCESS OPERATION!\n
                Got $this->accessmethod instead of POST, GET, GETALL, UPDATE, UPD, DELETE, DEL,\n
                SUM, SUMMARIZE, SUMMARY, SUMALL, SUMMARIZEALL, SUMMARYALL!",E_USER_ERROR);
                $response = 'trigger_error dosnt seem to work properly...';
                break;
        }
        return $response;
    }

    //<editor-fold desc="Calls">
    private function callPost($keys)
    {
        $subject = new PostNewTarget($keys);
        $subject
            ->setName($this->targetName)
            ->setWidth($this->width);
        if (!empty($this->image)) {
            $subject->setImage($this->Image);
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

    private function callGet($keys)
    {
        return (new GetTarget($keys))
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callGetAll($keys)
    {
        return (new GetAllTargets($keys))
            ->validateData()
            ->execute();
    }

    private function callUpdate($keys)
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

    private function callDelete($keys)
    {
        return (new DeleteTarget($keys))
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callSummary($keys)
    {
        return (new GetSummary($keys))
            ->setTargetId($this->targetId)
            ->validateData()
            ->execute();
    }

    private function callSummaryAll($keys)
    {
        return (new GetAllSummaries($keys))
            ->validateData()
            ->execute();
    }
    //</editor-fold>

    //<editor-fold desc="Fluent Setters">

    /**
     * @param string $accessmethod
     * @return vuforiaaccess
     */
    public function setAccessmethod($accessmethod):vuforiaaccess
    {
        if ($accessmethod != 'POST' &&
            $accessmethod != 'GET' &&
            $accessmethod != 'GETALL' &&
            $accessmethod != 'UPDATE' &&
            $accessmethod != 'DELETE')
            trigger_error("$this->accessmethod is not a valid AccessMethod for VuforiaAccess.\n
            Use POST, GET, GETALL, UPDATE or DELETE instead!");

        $this->accessmethod = $accessmethod;
        return $this;
    }

    /**
     * @param string $targetId
     * @return vuforiaaccess
     */
    public function setTargetId(string $targetId): vuforiaaccess
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * @param string $targetName
     * @return vuforiaaccess
     */
    public function setTargetName(string $targetName): vuforiaaccess
    {
        $this->targetName = $targetName;
        return $this;
    }

    /**
     * @param string $image // base 64 encoded
     * @return vuforiaaccess
     */
    public function setImage(string $image): vuforiaaccess
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param string $imagePath
     * @return vuforiaaccess
     */
    public function setImageByPath(string $imagePath): vuforiaaccess
    {
        $this->image = base64_encode(file_get_contents($imagePath));
        return $this;
    }

    /**
     * @param float $width
     * @return vuforiaaccess
     */
    public function setWidth(float $width): vuforiaaccess
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param string $meta
     * @return vuforiaaccess
     */
    public function setMeta(string $meta): vuforiaaccess
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param bool $activeflag
     * @return vuforiaaccess
     */
    public function setActiveflag(bool $activeflag): vuforiaaccess
    {
        $this->activeflag = $activeflag;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Getters">
    /**
     * @return string
     */
    public function getTargetId(): string
    {
        return $this->targetId;
    }

    /**
     * @return string
     */
    public function getTargetName(): string
    {
        return $this->targetName;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @return string
     */
    public function getMeta(): string
    {
        return $this->meta;
    }

    /**
     * @return int
     */
    public function getActiveflag(): int
    {
        return $this->activeflag;
    }

    /**
     * @return string
     */
    public function getAccessmethod(): string
    {
        return $this->accessmethod;
    }
    //</editor-fold>

}

//<editor-fold desc="Worker">
interface VuFoWorker {
    public function __construct($keys);
    public function execute();
    public function validateData();
}

//Create: Post
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

    function __construct($keys)
    {
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
        $this->url          = vuforiaaccess::getUrl();
        $this->requestPath  = vuforiaaccess::getTargetRequestPath();
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
            trigger_error("Active flag invalid! Defaulting to true");
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
}

//Read: GetAll
class GetAllTargets implements VuFoWorker {

    //Server Keys
    private $access_key;
    private $secret_key;

    private $url;
    private $requestPath;
    private $request;

    function __construct($keys)
    {
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
        $this->url          = vuforiaaccess::getUrl();
        $this->requestPath  = vuforiaaccess::getTargetRequestPath();
    }

    public function execute(){
        // this method felt really stupid to write :D
        return $this->execGetAllTargets();
    }

    public function validateData()
    {
        // GetAll can't fail validation, since there is no data we can validate :)
        return $this;
    }

    private function execGetAllTargets(){
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
}

//Read: Get
class GetTarget implements VuFoWorker {

    //Server Keys
    private $access_key;
    private $secret_key;

    private $targetId;
    private $url;
    private $requestPath;
    private $request;

    function __construct($keys)
    {
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
        $this->url          = vuforiaaccess::getUrl();
        $this->requestPath  = vuforiaaccess::getTargetRequestPath();
    }

    public function execute() {

        $this->requestPath = "$this->requestPath/$this->targetId";

        return $this->execGetTarget();
    }

    public function validateData()
    {
        if (!empty($this->targetId))
            trigger_error("no target ID set - invalid GET Request\nTo get a list of TargetIDs try GETALL",E_USER_ERROR);
        return $this;
    }

    private function execGetTarget(){

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
     * @return GetTarget
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }
}

//Read: SummarizeAll
class GetAllSummaries implements VuFoWorker {

    //Server Keys
    private $access_key;
    private $secret_key;

    private $url;
    private $requestPath;
    private $request;

    function __construct($keys)
    {
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
        $this->url          = vuforiaaccess::getUrl();
        $this->requestPath  = vuforiaaccess::getTargetSummaryPath();
    }

    public function execute(){
        // this method felt really stupid to write :D
        return $this->execSummarizeAllTargets();
    }

    public function validateData()
    {
        // GetAll can't fail validation, since there is no data we can validate :)
        return $this;
    }

    private function execSummarizeAllTargets(){
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
}

//Read: Summarize
class GetSummary implements VuFoWorker {

    //Server Keys
    private $access_key;
    private $secret_key;

    private $targetId;
    private $url;
    private $requestPath;
    private $request;

    function __construct($keys)
    {
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
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

//Update: Update
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

    function __construct($keys)
    {
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
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

//Delete: Delete
class DeleteTarget implements VuFoWorker {

    //Server Keys
    private $access_key;
    private $secret_key;

    private $url;
    private $requestPath;

    private $targetId;

    private $request; // internal

    function __construct($keys)
    {
        $this->access_key = $keys->access;
        $this->secret_key = $keys->secret;
        $this->url          = vuforiaaccess::getUrl();
        $this->requestPath  = vuforiaaccess::getTargetRequestPath();
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
}

//</editor-fold>

/**
 * Copyright (c) 2011-2013 Qualcomm Austria Research Center GmbH. All rights Reserved. Nothing in these materials is an offer to sell any of the components or devices referenced herein. Qualcomm is a trademark of QUALCOMM Incorporated, registered in the United States and other countries.Vuforia is a trademark of QUALCOMM Incorporated. Trademarks of QUALCOMM Incorporated are used with permission.
 * Vuforia SDK is a product of Qualcomm Austria Research Center GmbH. Vuforia Cloud Recognition Service is provided by Qualcomm Technologies, Inc..
 *
 * This Vuforia (TM) sample code provided in source code form (the "Sample Code") is made available to view for reference purposes only.
 * If you would like to use the Sample Code in your web application, you must first download the Vuforia Software Development Kit and agree to the terms and conditions of the License Agreement for the Vuforia Software Development Kit, which may be found at https://developer.vuforia.com/legal/license.
 * Any use of the Sample Code is subject in all respects to all of the terms and conditions of the License Agreement for the Vuforia Software Development Kit and the Vuforia Cloud Recognition Service Agreement.
 * If you do not agree to all the terms and conditions of the License Agreement for the Vuforia Software Development Kit and the Vuforia Cloud Recognition Service Agreement, then you must not retain or in any manner use any of the Sample Code.
 *
 */
class SignatureBuilder{

    private $contentType = '';
    private $hexDigest = 'd41d8cd98f00b204e9800998ecf8427e'; // Hex digest of an empty string

    public function tmsSignature( $request , $secret_key ){

        $method = $request->getMethod();
        // The HTTP Header fields are used to authenticate the request
        $requestHeaders = $request->getHeaders();
        // note that header names are converted to lower case
        $dateValue = $requestHeaders['date'];

        $requestPath = $request->getURL()->getPath();

        // Not all requests will define a content-type
        if( isset( $requestHeaders['content-type'] ))
            $this->contentType = $requestHeaders['content-type'];

        if ( $method == 'GET' || $method == 'DELETE' ) {
            // Do nothing because the strings are already set correctly
        } else if ( $method == 'POST' || $method == 'PUT' ) {
            // If this is a POST or PUT the request should have a request body
            $this->hexDigest = md5( $request->getBody() , false );
        } else {
            print("ERROR: Invalid content type passed to Sig Builder");
        }
        $toDigest = $method . "\n" . $this->hexDigest . "\n" . $this->contentType . "\n" . $dateValue . "\n" . $requestPath ;
        $shaHashed = "";
        try {
            // the SHA1 hash needs to be transformed from hexadecimal to Base64
            $shaHashed = $this->hexToBase64( hash_hmac("sha1", $toDigest , $secret_key) );
        } catch ( Exception $e) {
            $e->getMessage();
        }
        return $shaHashed;
    }


    private function hexToBase64($hex){
        $return = "";
        foreach(str_split($hex, 2) as $pair){
            $return .= chr(hexdec($pair));
        }
        return base64_encode($return);
    }
}
