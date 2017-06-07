<?php
require_once '../udvide.php';
header('Content-Type: application/xhtml+xml');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

</head>
<body>
<p>first 5 entries</p><br/>
<?php
$uv = new udvide();
$ar = $uv->getTargetPageByUser('test/tEditor','iAmBad');
foreach ($ar as $target) {
    echo '<div class="target"><p>';
    foreach ($target as $key=>$value) {
        echo $key . ' => ' . $value . ', ';
    }
    echo '</p><br/></div>';
}
?>
<br/>

username:<br/>
<input type="text" name="username" value="root"/>
    <br/>
pass hash:<br/>
<input type="text" name="passHash" value="imGoingToBePepperedAndSalted"/>
<br/>
udvide Subject:<br/>
<input type="text" name="subject" value="target"/>
<br/>
udvide Verb:<br/>
<input type="text" name="verb" value="create"/>
<br/>
target ID:<br/>
<input type="text" name="t_id" value="will not be read"/>
<br/>
target Name:<br/>
<input type="text" name="t_name" value="imFromHTML"/>
<br/>
target Image:<br/>
<input type="file" name="t_image" value="string of PNG JPG or anything gd supported"/>
<br/>
active Flag:<br/>
<input type="checkbox" name="activeFlag"/>
<br/>
x position:<br/>
<input type="text" name="xPos" value="150"/>
<br/>
y position:<br/>
<input type="text" name="yPos" value="80"/>
<br/>
map index:<br/>
<input type="text" name="map" value="test/1"/>
<br/>
content:<br/>
<input type="text" name="content" value="{'text'='hello world'}"/>
<br/>
<br/>
<button onclick="sendCmd()">Send</button>

<script>
    //<![CDATA[
    function sendCmd() {
        let target = {id:document.getElementById("t_id").value,
            name:document.getElementById("t_name").value,
            image:document.getElementById("t_image").value,
            activeFlag:};

        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("demo").innerHTML = this.responseText;
            }
        };
        xhttp.open("POST", "ajax_test.asp", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("fname=Henry&lname=Ford");
    }
    //]]>
</script>

</body>
</html>
