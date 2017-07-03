/**
 * Created by Elias on 29.06.2017.
 */
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
        + "&updateSubject=" + subject
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


function createEntry() {
    t_verb="create";
    loadMapSelect();
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
    loadUserUpdateField();
}

function updateUser(i) {
    updateSubject = userList[i].name;
    userList.splice(i,1);
    verb = "update";
    loadUserUpdateField();
}

function deleteUser(i){
    updateSubject = userList[i].name;
    userList.splice(i,1);
    sendAjax(user, subject, "delete", testSuccessful);
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
    let user = {
        passHash: document.getElementById("update_user_password").value,
        username: document.getElementById("update_user_name").value,
        role: document.getElementById("update_user_role").value,
        createTargetLimit: document.getElementById("update_user_tnumber").value,

    }
    sendAjax(user, "user", verb, testSuccessful);
    creatingUserCurrendtly = false;
    closeUserUpdateField();
}

