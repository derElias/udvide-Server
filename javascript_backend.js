// @script_author Simon
// treating bodies as random bodies requires colors.js at execution-time
let markerGenSettings = [
    // background color:
    [
        // treat values as random body, instead of a random list
        false,
        // values
        ["#222"]
    ],
    // fill of the triangles
    [
        // treat values as random body, instead of a random list
        true,
        // values
        [
            "#fff",
            "#aaa"
        ],
    ],
    // fill of the font
    [
        // treat values as random body, instead of a random list
        true,
        // values
        [
            "#4a4",
            "#573",
            "#375"
        ],
    ]
];

function markerGen_Sample() {
    // requires colors.js
    let test = document.getElementById('test');
    let img = document.createElement('img');
    img.src = generateMarker("I.2.2").toDataURL("image/jpeg", 0.95);
    test.appendChild(img);
}

function generateMarker(string) {
    let canvas = getNewMarkerCanvas(1000);
    let ctx = canvas.getContext("2d");
    ctx.font = "300px sans-serif";
    if (markerGenSettings[2][0]) {
        // get random color between
        ctx.fillStyle = colors_GetRandomColorBetween(markerGenSettings[2][1]);
    } else {
        // get a random entry from the provided values
        ctx.fillStyle = markerGenSettings[2][1]
            [Math.floor(Math.random() * markerGenSettings[0][1].length)];
    }
    ctx.textAlign = "center";
    ctx.fillText(string, canvas.width/2, canvas.height/2 + 50);
    ctx.strokeText(string, canvas.width/2 + 5 , canvas.height/2 + 45);
    return canvas;
}

let side;
function getNewMarkerCanvas(sideLength) {
    side = sideLength;
    let canvas = document.createElement("canvas");
    canvas.style.setProperty("width",side,"");
    canvas.setAttribute("width",side);
    canvas.setAttribute("height",side);
    if (canvas.getContext) {
        let context = canvas.getContext("2d");
        if (markerGenSettings[0][0]) {
            // get random color between
            context.fillStyle = colors_GetRandomColorBetween(markerGenSettings[0][1]);
        } else {
            // get a random entry from the provided values
            context.fillStyle = markerGenSettings[0][1]
                [Math.floor(Math.random() * markerGenSettings[0][1].length)];
        }
        context.fillRect(0,0,side,side);

        for (let i = 1; i < side; i += 3) {
            let x1,x2,x3,y1,y2,y3;
            // Get a start point
            x1 = Math.random() * side;
            y1 = Math.random() * side;

            // Get 2 x and y coordinates, which aren't too far away and do not overlap the border
            x2 = moarPts(x1,i);
            x3 = moarPts(x1,i);

            y2 = moarPts(y1,i);
            y3 = moarPts(y1,i);
            addTriangle(context,x1,y1,x2,y2,x3,y3);
        }
    }
    return canvas;
}

function moarPts(z,progress) {
    let val;
    let invalid;
    do {
        val = calcPtVal(z,progress);
        invalid =
               val > side
            || val < 0;
    } while (invalid);
    return val;
}
function calcPtVal(z,progress) {
    return z + randomPosNeg() * maxD(progress);
}

function maxD(progress) {
    return 40 * side / progress+50;
}
function randomPosNeg() {
    return (Math.random() * 2) - 1;
}

function addTriangle(context,x1,y1,x2,y2,x3,y3) {
    context.beginPath();
    context.moveTo(x1,y1);
    context.lineTo(x2,y2);
    context.lineTo(x3,y3);
    context.closePath();

    if (markerGenSettings[1][0]) {
        // get random color between
        context.fillStyle = colors_GetRandomColorBetween(markerGenSettings[1][1]);
    } else {
        // get a random entry from the provided values
        context.fillStyle = markerGenSettings[1][1]
            [Math.floor(Math.random() * markerGenSettings[1][1].length)];
    }

    context.fill();
    context.lineWidth = 5;
    context.stroke();
}
