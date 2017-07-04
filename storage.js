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


let verb;
let updateSubject;

let creatingUserCurrendtly = false;


function setMapList(printMethode) {
    sendAjax(null, "map", "readAll", function(){
        let response = JSON.parse(this.responseText);
        if (response.success === true) {
            mapList = response.payLoad;
            mapList.sort();
            printMethode();
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

function emptySorage() {
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
}