<?php
require_once 'helper.php';
header('Content-Type: application/xhtml+xml');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

</head>
<body>
<p>first 5 entries</p><br/>
<?php
$ar = (new udvide())->getTargetPageByUser('dev/simon');
foreach ($ar as $target) {
    echo '<div class="target"><p>';
    foreach ($target as $key=>$value) {
        echo $key . ' => ' . $value . ', ';
    }
    echo '</p><br/></div>';
}
?>
<br/>

<form action="/crudForm.php" method="post">
    <label>
        username:<br/>
        <input type="text" name="username" value="root"/>
    </label><br/>
    <label>
        pass hash:<br/>
        <input type="text" name="passHash" value="imGoingToBePepperedAndSalted"/>
    </label><br/>
    <label>udvide Verb:<br/>
        <input type="text" name="udvideVerb" value="create"/>
    </label><br/>
    <label>target ID:<br/>
        <input type="text" name="t_id" value="will not be read"/>
    </label><br/>
    <label>target Name:<br/>
        <input type="text" name="t_name" value="imFromHTML"/>
    </label><br/>
    <label>target Image:<br/>
        <input type="text" name="t_image" value="string of PNG JPG or anything gd supported"/>
    </label><br/>
    <label>active Flag:<br/>
        <input type="text" name="activeFlag" value="true"/>
    </label><br/>
    <label>x position:<br/>
        <input type="text" name="xPos" value="150"/>
    </label><br/>
    <label>y position:<br/>
        <input type="text" name="yPos" value="80"/>
    </label><br/>
    <label>map index:<br/>
        <input type="text" name="map" value="1"/>
    </label><br/>
    <label>content:<br/>
        <input type="text" name="content" value="{'text'='hello world'}"/>
    </label><br/>
    <br/>
    <input type="submit" value="Submit"/>
</form>

</body>
</html>
