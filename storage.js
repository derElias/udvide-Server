/**
 * Created by Elias on 29.06.2017.
 */
let username;
let passHash;

let view = 0;

let userList = null;
let mapList = null;
let targetList = null;

let image;

//To set when choosing Map
let map = null;
let xPos=0;
let yPos=0;

//needet for Ajax Requests
let verb;
let updateSubject;

let creatingUserCurrendtly = false;

let tempTarget = {
    name: null,
    image: null,
    activeFlag: null,
    xPos: null,
    yPos: null,
    map: null,
    content: null,
    mapImg: null
};

let activeMapContext=null;

function setMapList(printMethode) {
    sendAjax(null, "map", "readAll", function(){
        if (this.readyState === 4 && this.status === 200) {
            let response = JSON.parse(this.responseText);
            if (response.success === true) {
                mapList = response.payLoad;
                mapList.sort();
                printMethode();
            }
        }
    });
}

function setUserList(printMethode) {
    sendAjax(null, "user", "readAll", function(){
        if (this.readyState === 4 && this.status === 200) {
            let response = JSON.parse(this.responseText);

            if (response.success === true) {
                userList = response.payLoad;
                userList.sort();
                printMethode();
            }
    }});
}

function setTargetList(printMethode) {
    sendAjax(null, "target", "readAll", function(){
        if (this.readyState === 4 && this.status === 200) {
            let response = JSON.parse(this.responseText);
            if (response.success === true) {
                targetList = response.payLoad;
                targetList.sort();
                printMethode();
            }
    }});
}

function setTempTargetMap() {
    let i=document.getElementById("map_select").value;
    tempTarget.mapImg=mapList[i].image;
    loadTargetUpdateWindow();
    document.getElementById("map_imgPreview").src=tempTarget.mapImg;
    showMapPreview(function () {
        activeMapContext.fillStyle = "#FF0000";
        activeMapContext.fillRect(xPos-1,yPos-1,10,10);
    });

}

function setPosition(event) {
    var canvas = document.getElementById("mapCanvas");
    var rect = canvas.getBoundingClientRect();
    xPos = (event.clientX - rect.left) * canvas.width / rect.width;
    yPos = (event.clientY - rect.top) * canvas.height / rect.height;
    document.getElementById("demo").innerHTML = "X: " + xPos + ", Y: " + yPos;
    showMapPreview(function () {
        activeMapContext.fillStyle = "#FF0000";
        activeMapContext.fillRect(xPos-1,yPos-1,5,5);
    });
}

function emptyCRUDStorage(){
    immage=null;
    map = null;
    xPos=0;
    yPos=0;
    verb=null
    updateSubject=null;
    creatingUserCurrendtly = false;

    tempTarget = {
        name: null,
        image: null,
        activeFlag: null,
        xPos: null,
        yPos: null,
        map: null,
        mapImg:null,
        content: null
    };
}

function emptyStorage() {
    username="";
    passHash="";
    view = 0;
    userList = null;
    mapList = null;
    targetList = null;
    immage=null;
    map = null;
    xPos=0;
    yPos=0;
    verb=null
    updateSubject=null;
    creatingUserCurrendtly = false;

    tempTarget = {
        name: null,
        image: null,
        activeFlag: null,
        xPos: null,
        yPos: null,
        map: null,
        mapImg:null,
        content: null
    };
}