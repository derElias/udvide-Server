/**
 * Created by Elias on 29.06.2017.
 */

//lets the user load up a JPG for a marker and displays and on webpage
function markerPreviewFile() {
    let preview=document.getElementById("imgPreview");
    let file = document.querySelector('input[type=file]').files[0]; //same as here
    let reader = new FileReader();

    reader.onloadend = function () {
        image = reader.result;
        preview.src=image;
        document.getElementById("marker_downloadButton").href=image;
    }

    if (file) {
        reader.readAsDataURL(file); //reads the data as a URL
    } else {
        preview.src = "";
    }
}

//generates a marker and displays it to the webpage
function markerGeneration() {
    generateMarker(document.getElementById("imgPreview"), '', 1000, function () {
        document.getElementById("marker_downloadButton").href=document.getElementById("imgPreview").src;
    });
}

//lets the user load up a JPG for a map and displays it on webpage
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

//saves the selected map to local storage and calls showMapPriew()
function triggerMapPreview() {
    let i = document.getElementById("map_select").value;
    tempTarget.mapImg=mapList[i].image;
   showMapPreview(function() {});
}

//displays the image of the selected map on the webpage
function showMapPreview(f) {
    let img = document.createElement("img");
    let canvas = document.getElementById("mapCanvas");

    activeMapContext = canvas.getContext("2d");

    let background = new Image();
    background.onload = function () {

        img.src = canvas.toDataURL("image/jpeg", 0.95);
        canvas.setAttribute("width","" + background.width);
        canvas.setAttribute("height","" + background.height);
        activeMapContext.drawImage(background, 0, 0);
        f();
    };
    background.src = tempTarget.mapImg;
}

//adds or removes the the classname "entryboxMarked" to the corresponding HTML element
function toggleMarkEntry(i){
    console.log("toggle "+ i);
    let entry;
        entry = document.getElementsByClassName('entryboxTarget')[i];
        if(entry.classList.contains("entryboxMarked")){
            entry.classList.remove("entryboxMarked");
        }
        else {
            entry.classList.add("entryboxMarked");
        }
}

//adds or removes the the classname "entryboxMarked" to the corresponding HTML elements
function markEntries(i) {
    document.getElementsByClassName('entryboxUser')[i].classList.add("entryboxMarked");

    if (userList[i].role == 1) {
        if (userList[i].editors != false) {
            for (let k = 0; k < targetList.length; k++) {
                for (let l = 0; l < userList[i].editors.length; l++) {
                    if (userList[i].editors[l] == targetList[k].name) {
                        document.getElementsByClassName('entryboxTarget')[k].classList.add("entryboxMarked");
                    }
                }
            }
        }
    }
    else{
        for (let k = 0; k < targetList.length; k++) {
            document.getElementsByClassName('entryboxTarget')[k].classList.add("entryboxMarked");
        }
    }
}

//removes the the classname "entryboxMarked" from all HTML elements
function unmarkEverything() {
    let allMarkedEntrys = document.getElementsByClassName("entryboxMarked");
    let a = allMarkedEntrys.length;
    for(let i = 0; i < a; i++){
        allMarkedEntrys[0].classList.remove("entryboxMarked");
    }
}

