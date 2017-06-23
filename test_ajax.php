<?php
require_once 'udvide_old.php';
if (!SERVE_XHTML5_AS_HTML)
    header('Content-Type: application/xhtml+xml');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

</head>
<body>
<<<<<<< HEAD
<img src="img/preview.png" draggable="true"/>
=======
>>>>>>> 8313594b498b191d1a7ca2c2ffe226814b25bc89
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
        <label>target ID:<br/>
        <input type="text" id="t_id" value="will not be read"/></label>
        <br/>
        <label>target Name:<br/>
        <input type="text" id="t_name" value="imFromHTML"/></label>
        <br/>
        target Image:<br/>
        <div id="imageDnD" style="background-color: #777;width: 20vw;height: 20vw;position: relative;background-position: center;background-repeat: no-repeat;background-size: cover;">
            <p style="margin: auto;text-align: center;top: 7vw;position: absolute">Drag and Drop A Target Marker here</p>
        </div>
        <br/>
        <label>active Flag:<br/>
        <input type="checkbox" id="activeFlag" checked="checked"/></label>
        <br/>
        <label>x position:<br/>
        <input type="text" id="xPos" value="150"/></label>
        <br/>
        <label>y position:<br/>
        <input type="text" id="yPos" value="80"/></label>
        <br/>
        <label>map index:<br/>
        <input type="text" id="map" value="test/1"/></label>
        <br/>
        <label>content:<br/>
        <input type="text" id="content" value="{'text'='hello world'}"/></label>
        <br/>
        <br/>
    </div>
</div>
<button onclick="sendCmd()" style="float: left">Send</button>

<script>
    //<![CDATA[
    console.log("scripts work");

    // setup for Drag and Drop - storing result in image variable
    let file; // the file object
    let image; // the markers image as raw string
    let reader = new FileReader();
    let DnDbox = document.getElementById('imageDnD');

    // wait for DOM load completion before setting up DnD
    document.body.addEventListener('load', setupDnD());

    //<!--<editor-fold desc="DnD marker image">-->
    function setupDnD() {
<?php if (DEBUG_JS):?>console.log("DnD: EventListeners Work");<?php endif; ?>
        // Get file data on drop
        DnDbox.addEventListener('dragenter', function prevDef(e) {
            e.preventDefault();
        });

        DnDbox.addEventListener('dragover', function prevDefAndAddDropEffect(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        });

        DnDbox.addEventListener('drop', dropImage);
    }
    function dropImage(e) {
        e.stopPropagation();
        e.preventDefault();
<?php if (DEBUG_JS):?>console.log("DnD: drop");<?php endif; ?>
        let files = e.dataTransfer.files; // Array of all files
        if (files[1])
            customAlert("DnD: Multiple Files Droped: This Version can't process multiple - taking first file"); // ToDo: stretch: better solution?
        file = files[0]; // Take first

        if (file.type.match(/image.*/)) {
            console.log("match");

            reader.addEventListener('load', readerEventToPreview);
            reader.readAsDataURL(file); // start reading the file data.
        }
    }

    function readerEventToPreview(readEvent) {
        // finished reading file data.
<?php if (DEBUG_JS):?>console.log('DnD: Preview Handler');<?php endif; ?>
        DnDbox.style.backgroundImage = 'url(' + reader.result + ')';  // How to handle the Preview?
        image = reader.result;
        reader.removeEventListener('load', readerEventToPreview);

        //reader.addEventListener('load', readerEventFileStringToImage);
        //reader.readAsText(file);
    }

    function readerEventFileStringToImage(readEvent) {
        // finished reading file data.
        image = reader.result;
<?php if (DEBUG_JS):?>console.log('DnD: image set');<?php endif; ?>
        DnDbox.style.backgroundImage = image;

        reader.removeEventListener('load', readerEventFileStringToImage);
    }
    //<!--</editor-fold>-->

    function sendCmd() {
        let target = {
            id:document.getElementById("t_id").value,
            name:document.getElementById("t_name").value,
            image:image,
            activeFlag:document.getElementById("t_activeFlag").checked,
            xPos:document.getElementById("xPos").value,
            yPos:document.getElementById("yPos").value,
            map:document.getElementById("map").value,
            content:document.getElementById("t_content").value
        };

        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("fillme").innerHTML = this.responseText;
            }
        };
        let wwwForm =
            "username=" + document.getElementById("username").value
            + "&passHash=" + document.getElementById("passHash").value
            + "&subject=" + document.getElementById("subject").value
            + "&verb=" + document.getElementById("verb").value
            + "&target=" + JSON.stringify(target);
        console.log(wwwForm);
        let serverPage = 'ajax.php';
<?php if (GET_INSTEAD_POST): // docu: issue #34 ?>
        xhttp.open("GET", serverPage + "?" + wwwForm, true);
        xhttp.send();
<?php else: ?>
        xhttp.open("POST", serverPage, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); // One of the 2 possibilities for POST data to be transmitted via AJAX
        xhttp.send(wwwForm);
<?php endif; ?>
    }

    function customAlert(info) {
        console.log("alert: " + info);
        alert(info); // ToDo - make costum popup and stuff
    }
    //]]>
</script>

</body>
</html>
