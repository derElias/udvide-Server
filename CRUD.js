/**
 * Created by Elias on 29.06.2017.
 */

//object: object which is to be changed
//subject: typ of object
//verb: operation on object/subject
//callbackMathod: Method which is executed when the response arrives
function sendAjax(object, subject, verb, callbackMethod) {

    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = callbackMethod;
    let objSend = "";
    if (object == null)
        object = "";

    if (subject === "target") {
        objSend = "&target=";
    } else if (subject === "user") {
        objSend = "&user=";
    } else {
        objSend = "&map="
    }
    let wwwForm =
        "username=" + username
        + "&passHash=" + passHash
        + "&subject=" + subject
        + "&verb=" + verb;
    if (verb === "update") {
        wwwForm += "&updateSubject=" + updateSubject;
    }
    if (verb !== "readAll") {
        wwwForm += objSend + JSON.stringify(object);
    }
    let serverPage = 'ajax.php';
    xhttp.open("POST", serverPage, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); // One of the 2 possibilities for POST data to be transmitted via AJAX
    xhttp.send(wwwForm);
}

function selectMap() {
    let map = document.getElementbyId("mapSelect").value;
    console.log("map"+ map+"/n");
    console.log("map.image" +map.image+"/n");
    document.getElementById('m_imgPreview').src = map.image;
}

function  createTarget(){
    verb = "create";
    loadTargetUpdateWindow();
}

function updateTarget(i) {
    console.log(i);
    updateSubject = targetList[i].name;
    targetList.splice(i,1);
    verb = "update";
    loadTargetUpdateWindow();
}

function deleteTarget(i){
    console.log(i);
    updateSubject = targetList[i].name;
    targetList.splice(i,1);
    sendAjax(target, subject, "delete", testSuccessful);
}

function  createUser(){
    verb = "create";
    if (creatingUserCurrendtly == false) {
        creatingUserCurrendtly = true;
        loadUserUpdateField();
    }
}

function updateUser(i) {
    if (creatingUserCurrendtly == false) {
        creatingUserCurrendtly = true;
    }
    else {
        closeUserUpdateField();
    }
    updateSubject = userList[i];
    loadUserUpdateField();
    verb = "update";
}

function deleteUser(i){
    updateSubject = userList[i].name;
    userList.splice(i,1);
    sendAjax(updateSubject, "user", "delete", testSuccessful);
    deleteUserTableEntry(i);
}


function  createMap(){
    verb = "create";
    loadMapUpdateWindow();
}

function updateMap(i) {
    updateSubject = mapList[i].name;
    mapList.splice(i,1);
    verb = "update";
    loadMapUpdateWindow();
}

function deleteMap(i){
    updateSubject = mapList[i].name;
    mapList.splice(i,1);
    sendAjax(map, subject, "delete", testSuccessful);
}

function sendTargetCRUD() {
    let target = {
        name: document.getElementById("t_id").value,
        image: document.getElementById("t_imgPreview").src,
        activeFlag: document.getElementById("t_activeFlag").checked,
        xPos: xPos,
        yPos: yPos,
        map: map,
        content: document.getElementById("t_content").value
    };
    sendAjax(target, updateSubject, verb, testSuccessful);
    loadUserAndTargetTable();
}

function sendMapCRUD() {
    let map = {
        name: document.getElementById("map_id").value,
        image: document.getElementById("t_imgPreview").src
    };
    sendAjax(map, updateSubject, verb, testSuccessful);
    mapList.unshift(map);
    mapList.sort();
    loadMapTable();
}


function sendUserCRUD() {


    let pass =document.getElementById("update_user_password").value;
    if(pass=="") {pass=null;}
    let user = {
        passHash: pass,
        username: document.getElementById("update_user_name").value,
        role: document.getElementById("update_user_role").value,
        createTargetLimit: document.getElementById("update_user_tnumber").value
    }
    sendAjax(user, "user", verb, testSuccessful);
    creatingUserCurrendtly = false;
    closeUserUpdateField();
}

