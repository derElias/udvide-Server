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
    if (view == 0) {
    document.getElementById("viewSwitch").innerHTML = "Switch to<br/>Map-View";
    document.getElementById("content").innerHTML = resourcePackage.templates["entrytableAdmin.html"];
        if(userList == null){
            setUserList(printUserTable);
        }
        else {
            printUserTable();
        }
    }
    else{
        if(view==3){
            document.getElementById("content").innerHTML = resourcePackage.templates["entrytableEditor.html"];
        }
        printUser();
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
        temp.addEventListener("click", function() {
            clickedEntry(i,userList[i].username,"user");
        });
        temp.innerHTML = resourcePackage.templates["UserEntry.html"];
        parent.appendChild(temp);

        document.getElementsByClassName("user_title")[i].innerHTML = roleToString(userList[i].role) + ": " + userList[i].username;

        let updateButtonParent=document.getElementsByClassName('updateButtonUser')[i];
        let updateButton = document.createElement("img");
        updateButton.setAttribute("src","res/Entry.svg" );
        updateButtonParent.appendChild(updateButton);
        updateButtonParent.addEventListener("click", function() {
            updateUser(i);
        });

        let deleteParent=document.getElementsByClassName('deleteButtonUser')[i];
        let deleteButton= document.createElement("img");
        deleteButton.setAttribute("src","res/Delete.svg" );
        deleteParent.appendChild(deleteButton);
        deleteParent.addEventListener("click", function() {
            deleteUser(i);
        });
    }
}

function printUser() {
    let parent = document.getElementById('userList');
    let temp = document.createElement('div');
    temp.setAttribute('id', 'userElement'+0);
    temp.innerHTML = resourcePackage.templates["UserEntry.html"];
    parent.appendChild(temp);
    document.getElementsByClassName('user_title')[0].innerHTML = roleToString(userList[0].role) + ": " + userList[0].username;
}

function printTargetTable() {
    let parent = document.getElementById('targetList');

    for (let i = 0; i < targetList.length; i++) {
        let temp = document.createElement('div');
        temp.innerHTML = resourcePackage.templates["targetEntry.html"];
        temp.addEventListener("click", function() {
            clickedEntry(i,targetList[i].name,"target");
        });
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
    document.getElementById("content").innerHTML = resourcePackage.templates["targetUpdateWindow.html"];
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
    if(tempTarget.map != null){
        showMapPreview(function () {
            activeMapContext.fillStyle = "#FF0000";
            activeMapContext.fillRect(tempTarget.xPos-1,tempTarget.yPos-1,50,50);
        });
    }
}

function loadUserUpdateField() {
    let newItem = document.createElement("div");
    newItem.setAttribute("id", "createUserField");
    newItem.innerHTML = resourcePackage.templates["createUser.html"];

    let list = document.getElementById("userList");
    list.insertBefore(newItem, list.childNodes[0]);
    if(tempUser.username != null){
        document.getElementById("update_user_name").value = tempUser.username;
    }
    if(tempUser.role != null) {
        document.getElementById("update_user_role").selectedIndex =tempUser.role;
    }
    if(tempUser.createTargetLimit != null) {
        document.getElementById("update_user_tnumber").value = tempUser.createTargetLimit;
    }
    editorcheck();
}

function closeUserUpdateField() {
    let list=document.getElementById("userList");
    list.removeChild(list.childNodes[0]);
}

function editorcheck() {
    let item= document.getElementById("update_user_tnumber");
    let titles = document.getElementById("createUserField_titles");
    let values =document.getElementById("createUserField_values");
    let role=document.getElementById("update_user_role");
    if(role.value==1 && view ==0){
        titles.innerHTML="Name:</br>Password:</br>Role:</br>Tagretlimit:";
        item= document.createElement("input");
        item.setAttribute("id","update_user_tnumber");
        item.setAttribute("type","number");
        item.setAttribute("class","createUserInput");
        item.setAttribute("value",tempUser.createTargetLimit);
        values.appendChild(item);
    }
    else{
        if(item != null){
        titles.innerHTML="Name:</br>Password:</br>Role:";
        values.removeChild(item);
        }
    }
}

function loadMapUpdateWindow() {
    document.getElementById("content").innerHTML = resourcePackage.templates["createMap.html"];
    if(updateSubject!=null) {
        document.getElementById("map_name").value = updateSubject;
        document.getElementById("map_imgPreview").src = tempMap.image;
    }
}

function closeUpdateWindow() {

    if(creatingUserCurrendtly){
        closeUserUpdateField()
    }
    else{
        if(view == 0||view==3){
            loadUserAndTargetTable();
        }
        else {
            loadMapTable();
        }
    }
    emptyCRUDStorage();
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
        let dropdown=document.getElementById("map_select");
        console.log(dropdown.value);
        dropdown.selectedIndex=tempTarget.mapIndex;
        dropdown.value=tempTarget.mapIndex;

        showMapPreview(function () {
            activeMapContext.fillStyle = "#FF0000";
            activeMapContext.fillRect(tempTarget.xPos-1,tempTarget.yPos-1,5,5);
        });
    }
    console.log(tempTarget);
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
        view = 1;
        loadMapTable();
        emptyCRUDStorage();
    }
    else {
        view = 0;
        loadUserAndTargetTable();
        emptyCRUDStorage();
    }
}