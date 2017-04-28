<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.04.2017
 * Time: 20:54
 */

?>

<html>
<head>

</head>
<body>
<p style="color:green">udvide testpage</p><br>
<p><a href="mailto:udvide@web.de">MailtoLink</a></p><br>
<img src="img/logo.png"><br>
<svg
    height="100%"
    width="100%"
    version="1.1"
    viewBox="-50 -50 100 100"
    xmlns="http://www.w3.org/2000/svg"
    xmlns:xlink="http://www.w3.org/1999/xlink">
    <def>
    </def>
    <g id="light" >
        <g id="frame">
            <circle id="test" cx="0" cy="0" r="45"
                    style="fill:none;stroke:#000;stroke-width:6"/>
        </g>
        <g id="bulb" transform="scale(1.3)">
            <line
                style="stroke:#000;stroke-width:4"
                stroke-linecap="round"
                x1="-8"
                y1="17.5"
                x2="8"
                y2="17.5"/>
            <line
                style="stroke:#000;stroke-width:4"
                stroke-linecap="round"
                x1="-8"
                y1="22.5"
                x2="8"
                y2="22.5"/>
            <path
                style="stroke:#000;stroke-width:1"
                d="M-4,25.9a8,8 0,0,0 8,0z"
            />
            <path
                style="stroke:#000;stroke-width:2.2;fill:none"
                d="M-8,13.3q0,-4 -4,-12t-4,-12a16,16 0,1,1 32,0q0,4 -4,12t-4,12z"
            />
            <line
                style="stroke:#000;stroke-width:1"
                stroke-linecap="round"
                x1="-2"
                y1="13.5"
                x2="-2"
                y2="6"/>
            <line
                style="stroke:#000;stroke-width:1"
                stroke-linecap="round"
                x1="2"
                y1="13.5"
                x2="2"
                y2="6"/>
        </g>
    </g>
</svg>
<footer id="foot"></footer>
<script>

    window.onload = function () {
        document.getElementById("test").style.setProperty("stroke","#0f0");
    };
</script>
</body>
</html>
