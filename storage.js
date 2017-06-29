/**
 * Created by Elias on 29.06.2017.
 */
let username;
let passHash;

let view = 0;

let targetList = [];
let mapList = null;

let immage;

//To set when choosing Map
let x = null;
let y = null;
let map = null;

let xPos=0;
let yPos=0;

let verb;
let subject;

let creatingCurrendtly = false;
function setMapList(printMethode) {
    sendAjax(null, "map", "readAll", function(){
        let response = JSON.parse(this.responseText);
        if (response.success === true) {
            mapList = response.payLoad;
            printMethode();
        }
    });
}