/**
 * Created by Elias on 29.06.2017.
 */
let resourcePackage;
let username;
let passHash;
let permissions;

//to set when changing view
let view = 0;

//storage for users maps and targets
let userList = null;
let mapList = null;
let targetList = null;
let initial=null;

//temporary storage for a image
let image=null;

//To set when choosing Map
let map = null;
let xPos=0;
let yPos=0;

//needet for Ajax Requests
let verb=null;
let updateSubject=null;

//needet temporary storage for correct reacting in multiple functions
let creatingUserCurrendtly = false;
let subjectPermissions=null;
let subjectType=null;

//temporary storage for target-, user- and map-objects
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
//needet temporary storage for displaying the canvas of the map
let activeMapContext=null;


//sends first Ajax request and calls loadHeader() and loadUserAndTargetTable()
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
            if(userList.length==1 && userList[0].role==1){
                view =3;
            }

            loadUserAndTargetTable();
        }
    });
}

//sends Ajax request to get all maps, saves the values into the local storage and calls the given function
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

//sends Ajax request to get all users, saves the values into the local storage and calls the given function
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

//sends Ajax request to get all targets, saves the values into the local storage and calls the given function
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

//toggles the relation of a target to an editor on and of and saves the value into the local storage
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
            tempUser.editors.push(subject);
        }
    }
    else {
        tempUser.editors = [subject];
    }
}

//copies the values of the corresponding input fields into the local storage
function setTempTargetMap() {
    let i=document.getElementById("map_select").value;
    tempTarget.map=mapList[i].name;
    tempTarget.mapImg=mapList[i].image;
    tempTarget.mapIndex=i;
    tempTarget.xPos=xPos;
    tempTarget.yPos=yPos;
    loadTargetUpdateWindow();
}

//sets position of a target on a map and saves the values into the storage
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

//deletes all storage fields necessary for creating, updating, reading and deleting objects
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

//deletes all storage fields
function emptyStorage() {
    username="";
    passHash="";
    view = 0;
    userList = null;
    mapList = null;
    targetList = null;
    emptyCRUDStorage();
}