/**
 * Created by Elias on 29.06.2017.
 */
function switchView() {
    if (view == 0) {
        creatingUserCurrendtly = false;
        loadMapTable();
        view = 1;
    }
    else {
        loadUserAndTargetTable();
        view = 0;
    }
}

function markerPreviewFile() {
    let preview=document.getElementById("imgPreview");
    let file = document.querySelector('input[type=file]').files[0]; //same as here
    let reader = new FileReader();

    reader.onloadend = function () {
        image = reader.result;
        preview.src=image;
        document.getElementByID("marker_downloadButton").href=image;
    }

    if (file) {
        reader.readAsDataURL(file); //reads the data as a URL
    } else {
        preview.src = "";
    }
}

function mapPreviewFile() {
    let preview=document.getElementById("map_imgPreview");
    let file = document.querySelector('input[type=file]').files[0]; //same as here
    let reader = new FileReader();

    reader.onloadend = function () {
        image = reader.result;
        preview.src=image;
    }

    if (file) {
        reader.readAsDataURL(file); //reads the data as a URL
    } else {
        preview.src = "";
    }
}

function drawMapPoint(event) {

    var canvas = document.getElementById("mapCanvas");
    var ctx = canvas.getContext("2d");
    var rect = canvas.getBoundingClientRect();

    xPos = (event.clientX - rect.left) * canvas.width / rect.width;
    yPos = (event.clientY - rect.top) * canvas.height / rect.height;
    document.getElementById("demo").innerHTML = "X: " + xPos + ", Y: " + yPos;

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "#000000";
    ctx.fillRect(xPos-1,yPos-1,3,3);
    ctx.fillStyle = "#FF0000";
    ctx.fillRect(xPos-1,yPos-1,1,1);
}


function roleToString(role) {
    // ToDo read from lang file
    switch (role) {
        case 5:
            return '[root]';
        case 4:
            return '[Developer]';
        case 3:
            return '[Manager]';
        case 2:
            return '[Mod]';
        case 1:
            return '[Editor]';
        default:
            return ']HACKER[';
    }
}
