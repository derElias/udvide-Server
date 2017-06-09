<?php
require_once 'udvide.php';
header('Content-Type: application/xhtml+xml');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

</head>
<body>
<p>first 5 entries</p><br/>
<p id="fillme"></p>
<br/>
<div>
    <div style="width:25%;float: left">
        <label>username:<br/>
        <input type="text" id="username" value="root"/></label>
        <br/>
        <label>pass hash:<br/>
        <input type="text" id="passHash" value="imGoingToBePepperedAndSalted"/></label>
        <br/>
        <label>udvide Subject:<br/>
        <input type="text" id="subject" value="target"/></label>
        <br/>
        <label>udvide Verb:<br/>
        <input type="text" id="verb" value="create"/></label>
        <br/>
        <br/>
    </div>
    <div style="width:25%;float: left">
        target ID:<br/>
        <input type="text" id="t_id" value="will not be read"/>
        <br/>
        target Name:<br/>
        <input type="text" id="t_name" value="imFromHTML"/>
        <br/>
        target Image:<br/>
        <div id="t_image" style="background-color: #777;width: 20vw;height: 20vw;position: relative">
            <p style="margin: auto;text-align: center;top: 7vw;position: absolute">Drag and Drop A Target Marker here</p>
        </div>
        <br/>
        active Flag:<br/>
        <input type="checkbox" id="activeFlag"/>
        <br/>
        x position:<br/>
        <input type="text" id="xPos" value="150"/>
        <br/>
        y position:<br/>
        <input type="text" id="yPos" value="80"/>
        <br/>
        map index:<br/>
        <input type="text" id="map" value="test/1"/>
        <br/>
        content:<br/>
        <input type="text" id="content" value="{'text'='hello world'}"/>
        <br/>
        <br/>
    </div>
</div>
<button onclick="sendCmd()" style="float: left">Send</button>

<script>
    //<![CDATA[
    let image; // Stores the marker image
    // Get file data on drop
    document.getElementById('t_image').addEventListener('drop', function(e) {
        e.stopPropagation();
        e.preventDefault();
        let files = e.dataTransfer.files; // Array of all files
        file=files[0]; // Take first

        if (file.type.match(/image.*/)) {
            let reader = new FileReader();

            reader.onload = function(e2) {
                // finished reading file data.
                image = e2.target.result;

                let img = document.createElement('img');
                img.src= image;
                document.body.appendChild(img);
            };

            reader.readAsDataURL(file); // start reading the file data.
        }
    }
    });
    function sendCmd() {
        let target = {
            id:document.getElementById("t_id").value,
            name:document.getElementById("t_name").value,
            image:image,
            activeFlag:document.getElementById("activeFlag").checked,
            xPos:document.getElementById("xPos").value,
            yPos:document.getElementById("yPos").value,
            map:document.getElementById("map").value,
            content:document.getElementById("content").value
        };

        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("fillme").innerHTML = this.responseText;
            }
        };
        <?php
        $wwwForm = '"username=" + document . getElementById("username") . value
        + "&passHash=" + document . getElementById("passHash") . value
        + "&subject=" + document . getElementById("subject") . value
        + "&verb=" + document . getElementById("verb") . value
        + "&target=" + JSON . stringify(target)';
        $serverPage = 'ajax.php';
if (GET_INSTEAD_POST) { // docu: issue #34
    echo 'xhttp.open("GET", "' . $serverPage . '?" + ' . $wwwForm . ', true);' . "\n";
    echo 'xhttp.send();';
} else {
    echo 'xhttp.open("POST", "' . $serverPage . '", true);' . "\n";
    echo 'xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); // One of the 2 possibilities for POST data to be transmitted via AJAX' . "\n";
    echo 'xhttp.send(' . "\n" . $wwwForm . ');';
}
?>
    }
    //]]>
</script>

</body>
</html>
