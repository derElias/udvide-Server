<?php

echo time();
foreach ($_GET as $key => $value) {
    echo $key . ' -> ' . $value . ', <br/>';
}