/**
 * Created by Elias on 29.06.2017.
 */
function loadLogin() {
    document.getElementById("content").innerHTML = resourcePackage.templates["login.html"];
}

function loadHeader() {
    document.getElementById("header").innerHTML = resourcePackage.templates["headerContent.html"]
}

function emptyHeader() {
    document.getElementById("header").removeChild(document.getElementById("headerContent"))
}

function loadFooter() {
    document.getElementById("footer").innerHTML = resourcePackage.templates["footer.html"]
}

function loadUserAndTargetTable() {
    document.getElementById("viewSwitch").innerHTML="Switch to<br/>Map-View";
    document.getElementById("content").innerHTML = resourcePackage.templates["entrytableAdmin.html"];
    if(userList == null){
        setUserList(printUserTable);
    }
    else {
        printUserTable();
    }

    if(targetList == null){
        setTargetList(printTargetTable);
    }
    else {
        printTargetTable();
    }
}

//<editor-fold desc="print User Table">
function printUserTable() {
    let parent = document.getElementById('userList');

    for (let i = 0; i < userList.length; i++) {
        let temp = document.createElement('div');
        temp.setAttribute('id', 'userElement'+i);
        temp.innerHTML = resourcePackage.templates["UserEntry.html"];
        parent.appendChild(temp);

        document.getElementsByClassName('user_title')[i].innerHTML = roleToString(userList[i].role) + ": " + userList[i].username;
        document.getElementsByClassName('updateButtonUser')[i].addEventListener("click", function() {
            updateUser(i);
        });
        document.getElementsByClassName('deleteButtonUser')[i].addEventListener("click", function() {
            deleteUser(i);
        });
    }
}

function deleteUserTableEntry(i) {
        document.getElementById('userList').removeChild(document.getElementById('userElement'+i));
}

function printTargetTable() {
    let parent = document.getElementById('targetList');

    for (let i = 0; i < targetList.length; i++) {
        let temp = document.createElement('div');
        temp.innerHTML = resourcePackage.templates["targetEntry.html"];
        parent.appendChild(temp);
        targetList[i] = target.fromArray(targetList[i]);
        document.getElementsByClassName('targetEntry')[i].innerHTML = targetList[i].name;
        document.getElementsByClassName('updateButtonTarget')[i].addEventListener("click", function() {
            updateTarget(i);
        });
        document.getElementsByClassName('deleteButtonTarget')[i].addEventListener("click", function() {
            deleteTarget(i);
        });
    }
}

function printMapTable() {

    let parent = document.getElementById('mapList');
    for (let i = 0; i < mapList.length; i++) {
        let temp = document.createElement('div');
        temp.innerHTML = resourcePackage.templates["mapEntry.html"];
        parent.appendChild(temp);
        document.getElementsByClassName('map_title')[i].innerHTML = mapList[i].name;
        document.getElementsByClassName('updateButtonMap')[i].addEventListener("click", function() {
            updateMap(i);
        });
        document.getElementsByClassName('deleteButtonMap')[i].addEventListener("click", function() {
            deleteMap(i);
        });
    }
    document.getElementById("viewSwitch").innerHTML="Switch to<br/>Main-View";
}

function printMapOptions() {
    let parent=document.getElementById('map_select');
    for (let i = 0; i < mapList.length; i++) {
        let temp = document.createElement('option');
        temp.setAttribute("class","map_selectOption");
        temp.setAttribute("value",i);
        parent.appendChild(temp);
        let option=document.getElementsByClassName('map_selectOption')[i];
        option.innerHTML = mapList[i].name;
    }
}

function printLoginFail(){
    document.getElementById("loginWarning").innerHTML = "Login Fehlgeschlagen!!!";
}

function loadTargetUpdateWindow() {
    console.log(tempTarget);
    document.getElementById("content").innerHTML = resourcePackage.templates["TargetUpdateWindow.html"];
    if(tempTarget.name != null){
        document.getElementById("target_name").value=tempTarget.name;
    }
    if(tempTarget.activeFlag != null){
        document.getElementById("t_activeFlag").checked=tempTarget.activeFlag;
    }
    if(tempTarget.content != null){
        document.getElementById("t_content").value=tempTarget.content;
    }
    if(tempTarget.image != null){
        document.getElementById("imgPreview").src=tempTarget.image;
    }
}

function loadUserUpdateField() {
    let newItem = document.createElement("div");
    newItem.setAttribute("id", "createUserField");
    newItem.innerHTML = resourcePackage.templates["createUser.html"];

    let list = document.getElementById("userList");
    list.insertBefore(newItem, list.childNodes[0]);
    document.getElementById("update_user_name").setAttribute("value", updateSubject.username);
    document.getElementById("update_user_role").setAttribute("value", updateSubject.role);
    document.getElementById("update_user_role").selectedIndex="value", updateSubject.role;
    document.getElementById("update_user_tnumber").setAttribute("value", updateSubject.createTargetLimit);
}

function closeUserUpdateField() {
    document.getElementById("userList").removeChild(document.getElementById("createUserField"));
    updateSubject=null;
}

function loadMapUpdateWindow() {
    document.getElementById("content").innerHTML = resourcePackage.templates["createMap.html"];
    if(updateSubject!=null) {
        document.getElementById("map_name").setAttribute("value", updateSubject.name);
        document.getElementById("map_imgPreview").setAttribute("src", updateSubject.image);
    }
}

function loadMapTable() {
    document.getElementById("content").innerHTML = resourcePackage.templates["mapTableTempl.html"];
    if(mapList === null) {
        setMapList(printMapTable);
    }
    else {
        printMapTable();
    }
}

function loadMapSelect() {
    tempTarget.name = document.getElementById("target_name").value;
    tempTarget.image = document.getElementById("imgPreview").src;
    tempTarget.activeFlag = document.getElementById("t_activeFlag").checked;
    tempTarget.content = document.getElementById("t_content").value;

    document.getElementById("content").innerHTML = resourcePackage.templates["selectMap.html"];
    if(mapList == null) {
        setMapList(printMapOptions);
    }
    else {
        printMapOptions();
    }

    if(tempTarget.map != null){
        document.getElementById("map_select").selectedIndex="value", tempTarget.map.name;
        document.getElementById("m_imgPreview").src = tempTarget.map.image;
    }
}

function testSuccessful(){
    if (this.readyState === 4 && this.status === 200) {
        let response = JSON.parse(this.responseText);

        console.log(response);

        if (response.success === false) {
            alert("action: " + verb + " unssuccessfull")
        }
        else {
            verb=null;
        }
    }
}

function switchView() {
    if (view == 0) {
        loadMapTable();
        view = 1;
        emptyCRUDStorage();
    }
    else {
        loadUserAndTargetTable();
        view = 0;
        emptyCRUDStorage();
    }
}