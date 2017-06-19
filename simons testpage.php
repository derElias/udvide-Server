<?php
require_once 'udvide.php';

$ud = new udvide();
echo time();
?>
<div id="drop_zone">Drop files here</div>
<output id="list"></output>

<script>
    function sendCmd() {
        let wwwForm = '';
        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let tn = document.createTextNode(JSON.parse(this.responseText).res["Create.svg"]);
                document.getElementById("list").appendChild(tn);
            }
        };
        let serverPage = 'resourcePackage.php';
        <?php if (GET_INSTEAD_POST): // docu: issue #34 ?>
        xhttp.open("GET", serverPage + "?" + wwwForm, true);
        xhttp.send();
        <?php else: ?>
        xhttp.open("POST", serverPage, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); // One of the 2 possibilities for POST data to be transmitted via AJAX
        xhttp.send(wwwForm);
        <?php endif; ?>
    }
    sendCmd();
</script>