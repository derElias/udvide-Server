/**
 * Created by Elias on 29.06.2017.
 */
let username;
let passHash;
let permissions;

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
let verb
let updateSubject=null;
let subjectType=null;
let subjectPermissions=null;

let creatingUserCurrendtly = false;

let initial=null;

let tempTarget = {
    name: null,
    image: null,
    activeFlag: null,
    content: null,
    xPos: null,
    yPos: null,
    map: null,
    mapImg: null,
    mapIndex: null
};

let tempUser ={
    username: null,
    role: null,
    createTargetLimit: null,
    editors: null
};

let tempMap ={
    name: null,
    img: null
};

let activeMapContext=null;

function initialRead() {
    sendAjax(null, "initial", "readAll", function () {
        if (this.readyState === 4 && this.status === 200) {
            initial = JSON.parse(this.responseText);
            userList = initial.payLoad.users;
            targetList = initial.payLoad.targets;
            mapList = initial.payLoad.maps;
            for(let i=0; i<userList.length;i++){
                if(username==userList[i].username){
                    permissions = userList[i].role;
                }
            }

            loadHeader();
            if(userList[0].role == 1){
                view =3;
            }
            loadUserAndTargetTable();
        }
    });
}

function setMapList(printMethode) {
    sendAjax(null, "map", "readAll", function(){
        if (this.readyState === 4 && this.status === 200) {
            let response = JSON.parse(this.responseText);
            if (response.success === true) {
                mapList = response.payLoad;
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
                printMethode();
            }
    }});
}

function toggleAssingment(subject) {
    if (tempUser.editors != false) {
        let toggled = false;
        for (let i = 0; i < tempUser.editors.length; i++) {
            if (tempUser.editors[i] == subject) {
                tempUser.editors.splice(i, 1);
                toggled = true
            }
        }
        if (toggled == false) {
            tempUser.editors.add(subject);
        }
    }
    else {
        tempUser.editors = [subject];
    }
}

function setTempTargetMap() {
    let i=document.getElementById("map_select").value;
    tempTarget.map=mapList[i].name;
    tempTarget.mapImg=mapList[i].image;
    tempTarget.xPos=xPos;
    tempTarget.yPos=yPos;
    loadTargetUpdateWindow();
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
    subjectType=null;
    subjectPermissions=null;
    creatingUserCurrendtly = false;

    tempTarget = {
        name: null,
        image: null,
        activeFlag: null,
        content: null,
        xPos: null,
        yPos: null,
        map: null,
        mapImg: null,
        mapIndex: null
    };

    tempUser ={
        username: null,
        role: null,
        createTargetLimit: null,
        editors: null
    };

    tempMap ={
        name: null,
        image: null
    };
    unmarkEverything();
}

function emptyStorage() {
    username="";
    passHash="";
    view = 0;
    userList = null;
    mapList = null;
    targetList = null;
    emptyCRUDStorage();
}