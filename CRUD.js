/**
 * Created by Elias on 29.06.2017.
 */

// global var updatesubject is name of old subject
//object: the new objectdata which is to be send
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
    emptyCRUDStorage();
    verb = "create";
    loadTargetUpdateWindow();
}

function updateTarget(i) {
    emptyCRUDStorage();
    verb = "update";
    updateSubject=targetList[i].name;
    sendAjax(targetList[i],"target","read",function () {
        if (this.readyState === 4 && this.status === 200) {
            tempTarget= JSON.parse(this.responseText);
            if(tempTarget.map !=null){
                for (let i =o; i < mapList.length; i++){
                    if(tempTarget.map == mapList[i].name){
                        tempTarget.mapIndex=i;
                        tempTarget.mapImg=mapList[i].image;
                    }
                }
            }

            loadTargetUpdateWindow();
        }
    });
}

function deleteTarget(i){
    emptyCRUDStorage();
    updateSubject=targetList[i].name;
    let target=targetList[i];
    targetList.splice(i,1);
    sendAjax(target, "target", "delete", function () {
        if (this.readyState === 4 && this.status === 200) {
            loadUserAndTargetTable();
        }});
}

function  createUser(){
    console.log(creatingUserCurrendtly)
    if (creatingUserCurrendtly) {
    closeUserUpdateField();
}
    emptyCRUDStorage();
    loadUserUpdateField();
    creatingUserCurrendtly =true;
    verb = "create";
}

function updateUser(i) {
    if (creatingUserCurrendtly){
        closeUserUpdateField();
    }
    emptyCRUDStorage();
    creatingUserCurrendtly =true;
    verb = "update";
    tempUser=userList[i];
    loadUserUpdateField();
    //clickedEntry(0,userList[i].username,"user");
}

function deleteUser(i) {
    emptyCRUDStorage();
    updateSubject = userList[i].username;
    let user=userList[i];
    userList.splice(i, 1);
    sendAjax(user, "user", "delete", function () {
        if (this.readyState === 4 && this.status === 200) {
            loadUserAndTargetTable();
        }
    });
}

function  createMap(){
    emptyCRUDStorage();
    verb = "create";
    loadMapUpdateWindow();
}

function updateMap(i) {
    emptyCRUDStorage();
    updateSubject = mapList[i].name;
    tempMap=mapList[i];
    verb = "update";
    loadMapUpdateWindow();
}

function deleteMap(i){
    emptyCRUDStorage();
    updateSubject = mapList[i].name;
    let map = mapList[i];
    mapList.splice(i,1);
    sendAjax(map, "map", "delete", function () {
        if (this.readyState === 4 && this.status === 200) {
            loadMapTable();
        }});
}

function sendTargetCRUD() {
    let target = {
        name: document.getElementById("target_name").value,
        image: document.getElementById("imgPreview").src,
        activeFlag: document.getElementById("t_activeFlag").checked,
        xPos: tempTarget.xPos,
        yPos: tempTarget.yPos,
        map: tempTarget.map,
        content: document.getElementById("t_content").value
    };
    if(verb=="update") {
        for (let i = 0; i < targetList.length; i++) {
            if (targetList[i].name == updateSubject.name) {
                targetList.splice(i, 1);
            }
        }
    }
    sendAjax(target, "target", verb, testSuccessful);
    loadUserAndTargetTable();
    emptyCRUDStorage();
}

function sendMapCRUD() {
    let newMap = {
        name: document.getElementById("map_name").value,
        image: document.getElementById("map_imgPreview").src
    };
    if(verb=="update") {
        for (let i = 0; i < mapList.length; i++) {
            if (mapList[i].name == updateSubject) {
                mapList.splice(i, 1);
            }
        }
    }
    sendAjax(newMap, "map", verb, testSuccessful);
    mapList.unshift(newMap);
    loadMapTable();
    emptyCRUDStorage();
}


function sendUserCRUD() {
    let pass =document.getElementById("update_user_password").value;
    if(pass==""){pass=null;}
    let role = document.getElementById("update_user_role").value;
    let user;
    if(role ==1) {
        user = {
            passHash: pass,
            username: document.getElementById("update_user_name").value,
            role: role,
            createTargetLimit: document.getElementById("update_user_tnumber").value
        };
    }
    else{
        user = {
            passHash: pass,
            username: document.getElementById("update_user_name").value,
            role: role,
        };
    }

    sendAjax(user, "user", verb, function () {
        if (this.readyState === 4 && this.status === 200) {
            emptyCRUDStorage();
        }});
    creatingUserCurrendtly = false;
    userList.unshift(user);
    if(verb=="update"){
        for(let i = 0; i < userList.length; i++){
            if(userList[i].username == updateSubject){
                userList.splice(i, 1);
            }
        }
        closeUserUpdateField();
    }
    loadUserAndTargetTable();
}

function sendEditorUpdate() {
    sendAjax(tempUser,"user","update", function(){});
}

function clickedEntry(i,subject, entryType) {

    if (entryType == "user" && view ==0) {
        if (updateSubject == null) {
            tempUser = userList[i];
            updateSubject = subject;
            subjectPermissions = userList[i].role;
            markEntries(i);
        }
        else {
            if (subject != updateSubject) {
                sendEditorUpdate();
                unmarkEverything();
                tempUser=userList[i];
                updateSubject = subject;
                subjectPermissions = userList[i].role;
                markEntries(i);
            }
            else{
                sendEditorUpdate();
                emptyCRUDStorage();
            }
        }
    }
    else {
        if (updateSubject != null) {
            toggleAssingment(i);
            toggleMarkEntry(i);
        }
    }
}