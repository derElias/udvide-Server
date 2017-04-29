<html>
<head>
<style>

body{
  font-family:verdana;
}

.result{
  font-family:courier;
  color:green;
}
</style>
</head>
<body>
<h3>VWS Samples in PHP using HTTP_Request2</h3>
<br />
<br />
<br />


<?php
require_once 'GetTarget.php';
require_once 'UpdateTarget.php';
require_once 'DeleteTarget.php';
require_once 'PostNewTarget.php';
require_once 'GetAllTargets.php';


$instance = null;

if( isset( $_GET['select']) ){
	
	$selection = $_GET['select'];
	
	echo "<div> $selection RESULT :</div><br/>";
	
	echo "<div class='result'>";
	
	switch( $selection ){
		
		case "GetTarget" :
			$instance = new GetTarget();
			break;
		case "UpdateTarget" :
			$instance = new UpdateTarget();
			break;
		case "DeleteTarget" :
			$instance = new DeleteTarget();
			break;
		case "PostNewTarget" :
			$instance = new PostNewTarget();
			break;
		case "GetAllTargets" :
			$instance = new GetAllTargets();
			break;
		default :
			echo "INVALID SELECTION";
			break;
		
	}
	
	echo "</div>";
	
	
	echo "<br /><div>~~~~~~~~~~~~~~~</div><br />";
	
}

?>


<div>Samples:</div>
<br />
<div>
<a href="SampleSelector.php?select=GetTarget"><b>GetTarget.php</b> queries a single target by target id.</a>
</div>
<br />
<div>
<a href="SampleSelector.php?select=GetAllTargets"><b>GetAllTargets.php</b> queries for all target ids in a Cloud Reco database.</a>
</div>
<br />
<div>
<a href="SampleSelector.php?select=UpdateTarget"><b>UpdateTarget.php</b> updates the metadata for a target.</a>
</div>
<br />
<div>
<a href="SampleSelector.php?select=DeleteTarget"><b>DeleteTarget.php</b> deletes a target from its Cloud Database.</a>
</div>
<br />
<div>
<a href="SampleSelector.php?select=PostNewTarget"><b>PostNewTarget.php</b> uploads a new target to a Cloud Database.</a>
</div>
<br />
<br />
<br />
<br />
<div>
<a href="CheckPHPEnvironment.php">Review your PHP installation.</a>
</div>
</body>
</html>