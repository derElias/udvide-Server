/**
 * Created by Elias on 29.06.2017.
 */
var t_verb;
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

function saveEntry() {
    let target = {
        name: document.getElementById("t_id").value,
        image: document.getElementById("t_imgPreview").src,
        activeFlag: document.getElementById("t_activeFlag").checked,
        xPos: x,
        yPos: y,
        map: map,
        content: document.getElementById("t_content").value
    };
    sendAjax(target, target.name, "create");
    loadUserAndTargetTable();
}

function  createMap(){
    verb = "create";
    loadMapUpdateWindow();
}

function updateMap(i) {
    subject = mapList[i].name;
    mapList.splice(i,1);
    verb = "update";
    loadMapUpdateWindow();
}

function deleteMap(i){
    subject = mapList[i].name;
    mapList.splice(i,1);
    verb = "delete";
    sendAjax(map, subject, verb, testSuccessful);
}

function sendMapCRUD() {
    let map = {
        name: document.getElementById("map_id").value,
        image: document.getElementById("t_imgPreview").src
    };
    sendAjax(map, subject, verb, testSuccessful);
    mapList.unshift(map);
    mapList.sort();
    loadMapTable();
}


function sendUserCreate() {
    let user = {
        passHash: document.getElementById("update_user_password").value,
        username: document.getElementById("update_user_name").value,
        role: document.getElementById("update_user_role").value,
        createTargetLimit: document.getElementById("update_user_tnumber").value,

    }
    sendAjax(user, user.username, "create");
    creatingCurrendtly = false;
    closeUserUpdateField();
}

