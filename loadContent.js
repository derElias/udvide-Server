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
    document.getElementById("content").innerHTML = resourcePackage.templates["entrytableTempl.html"];
    sendAjax(null, "user", "readAll", printUserTable);
    sendAjax(null, "target", "readAll", printTargetTable);
}

//<editor-fold desc="Load User Table">
function printUserTable() {
    if (this.readyState === 4 && this.status === 200) {
        let response = JSON.parse(this.responseText);

        if (response.success === true) {
            let payLoad = response.payLoad;
            let parent = document.getElementById('userList');

            for (let i = 0; i < payLoad.length; i++) {
                let temp = document.createElement('div');
                temp.innerHTML = resourcePackage.templates["User.html"];
                parent.appendChild(temp);
                document.getElementsByClassName('user_title')[i].innerHTML =
                    roleToString(payLoad[i].role) + ": " + payLoad[i].username;
            }
        }
    }
}

function printTargetTable() {
    if (this.readyState === 4 && this.status === 200) {
        let response = JSON.parse(this.responseText);

        if (response.success === true) {
            let payLoad = response.payLoad;
            let parent = document.getElementById('targetList');

            for (let i = 0; i < payLoad.length; i++) {
                let temp = document.createElement('div');
                temp.innerHTML = resourcePackage.templates["Entry.html"];
                parent.appendChild(temp);
                targetList[i] = target.fromArray(payLoad[i]);
                document.getElementsByClassName('targetEntry')[i].innerHTML =
                    targetList[i].name;
            }
        }
    }
}

function loadUserUpdateField() {
    if (creatingCurrendtly == false) {
        let newItem = document.createElement("div");
        newItem.setAttribute("id", "createUserField")
        newItem.innerHTML = resourcePackage.templates["createUser.html"];

        let list = document.getElementById("userList");
        list.insertBefore(newItem, list.childNodes[0]);
        creatingCurrendtly = true;
        }
}

function closeUserUpdateField() {
    document.getElementById("userList").removeChild(document.getElementById("createUserWindow"))
}

function loadEntryUpdatePopup() {
    document.getElementById("content").innerHTML = resourcePackage.templates["EntryPopup.html"];
}

function loadMapUpdateWindow() {
    document.getElementById("content").innerHTML = resourcePackage.templates["createMap.html"];
}

function loadMapTable() {
    document.getElementById("content").innerHTML = resourcePackage.templates["mapTableTempl.html"];
    sendAjax(null, "map", "readAll", printMapTable);
}

function printMapTable() {
    if (this.readyState === 4 && this.status === 200) {
        let response = JSON.parse(this.responseText);

        if (response.success === true) {
            let payLoad = response.payLoad;
            let parent = document.getElementById('mapList');

            for (let i = 0; i < payLoad.length; i++) {
                let temp = document.createElement('div');
                temp.innerHTML = resourcePackage.templates["mapEntry.html"];
                parent.appendChild(temp);
                document.getElementsByClassName('map_title')[i].innerHTML = payLoad[i].name;
                document.getElementsByClassName('updateButtonMap')[i].addEventListener(updateMap(i));
                document.getElementsByClassName('updateButtonMap')[i].addEventListener(deleteMap(i));
            }
        }
    }
}

function loadMapSelect() {
    document.getElementById("content").innerHTML = resourcePackage.templates["selectMap.html"];
    if(mapList == null) {
        setMapList(printMapOptions);
    }
    else {
        printMapOptions();
    }
}

function printMapOptions() {
    console.log(mapList.payLoad);
    console.log(mapList.payLoad[1]);
    for (let i = 0; i < payLoad.length; i++) {
        let temp = document.createElement('option');
        temp.setAttribute("class","mapSelectoption");
        temp.setAttribute("value",""+i);
        temp.setAttribute("id","mapSelectOption"+i);
        parent.appendChild(temp);
        document.getElementsByClassName('map_selectOption')[i].innerHTML = payLoad[i].name;
    }
}