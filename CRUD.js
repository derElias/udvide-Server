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
    document.getElementById('m_imgPreview').src = map.image;
}

function  createTarget(){
    verb = "create";
    loadTargetUpdateWindow();
}

function updateTarget(i) {
    sendAjax(targetList[i].name,"target","read",function () {
        if (this.readyState === 4 && this.status === 200) {
            console.log(this.responseText);
            updateSubject = JSON.parse(this.responseText);
            loadTargetUpdateWindow();
        }
    });
    verb = "update";
}

function deleteTarget(i){
    updateSubject = targetList[i].name;
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
        sendAjax(targetList[i].name,"target","read",function () {
            if (this.readyState === 4 && this.status === 200) {
                updateSubject = JSON.parse(this.responseText);
                closeUserUpdateField();
            }
        });
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
    updateSubject = mapList[i];
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
    let newMap = {
        name: document.getElementById("map_name").value,
        image: document.getElementById("map_imgPreview").src
    };
    sendAjax(newMap, updateSubject, verb, testSuccessful);
    mapList.unshift(map);
}


function sendUserCRUD() {
    let pass =document.getElementById("update_user_password").value;
    if(pass==""){pass=null;}
    let user = {
        passHash: pass,
        username: document.getElementById("update_user_name").value,
        role: document.getElementById("update_user_role").value,
        createTargetLimit: document.getElementById("update_user_tnumber").value
    }
    sendAjax(user, "user", verb, testSuccessful);
    creatingUserCurrendtly = false;
    closeUserUpdateField();
    userList.unshift(user);
    if(updateSubject!=null){
        for(let i = 0; i < userList.length; i++){
            if(userList[i].username == updateSubject.username){
                deleteUserTableEntry(i);
            }
        }
        updateSubject=null;
    }
}

