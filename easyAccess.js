let resourcePackage;
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
        document.getElementById("test").appendChild(document.createTextNode(this.responseText));
        resourcePackage = JSON.parse(this.responseText);
        main();
    }
};
xhttp.open("POST", "resourcePackage.php", true);
xhttp.send();

function main() {
    loadLogin();
    loadFooter();
}

function createMap() {
    document.getElementById("content").innerHTML = resourcePackage.templates["createMap.html"];
}

function saveMap() {
    let map = {
        name: document.getElementById("map_id").value,
        image: document.getElementById("t_imgPreview").src
    };
    sendAjax(map, map.name, "create");
    document.getElementById("content").innerHTML = resourcePackage.templates["mapTableTempl.html"];

}

function createEntry() {
    getMapSelect();
}

function getMapSelect() {
    document.getElementById("content").innerHTML = resourcePackage.templates["selectMap.html"]
}
let username;
let passHash;
function login() {
    username = document.getElementById("login_username").value;
    passHash = document.getElementById("login_password").value;
    getEntryTable();
    getTargetTable();
    loadHeader();
}

function loadFooter() {
    document.getElementById("footer").innerHTML = resourcePackage.templates["footer.html"]
}

function logout() {
    username = "";
    passHash = "";
    loadLogin();
    emptyHeader();
}
function loadHeader() {
    document.getElementById("header").innerHTML = resourcePackage.templates["headerContent.html"]
}

function emptyHeader() {
    document.getElementById("header").removeChild(document.getElementById("headerContent"))
}

let creatingCurrendtly = false;
function createUser() {
    if (creatingCurrendtly == false) {

        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let newItem = document.createElement("div");
                newItem.setAttribute("id", "createUserWindow")
                newItem.innerHTML = this.responseText;

                let list = document.getElementById("userList");
                list.insertBefore(newItem, list.childNodes[0]);
                creatingCurrendtly = true;
            }
        };
        xhttp.open("GET", "templates/createUser.html", true);
        xhttp.send();
    }
}


function loadLogin() {
    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("content").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/login.html", true);
    xhttp.send();
}

//To set when choosing Map
let x = null;
let y = null;
let map = null;

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
    getEntryTable();
}

//<editor-fold desc="Load User Table">
function getEntryTable() {
    document.getElementById("content").innerHTML = resourcePackage.templates["entrytableTempl.html"];
    sendAjax(null, "user", "getAll", printEntryTable);
}
function printEntryTable() {
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

function roleToString(role) {
    // ToDo read from lang file
    switch (role) {
        case 5:
            return '[root]';
        case 4:
            return '[Developer]';
        case 3:
            return '[Manager]';
        case 2:
            return '[Mod]';
        case 1:
            return '[Editor]';
        default:
            return ']HACKER[';
    }
}
//</editor-fold>

//<editor-fold desc="Load Target Table">
class target {
    static fromArray(arr) {
        let instance = new this();
        instance.name = arr.name;
        instance.owner = arr.owner;
        instance.xPos = arr.xPos;
        instance.yPos = arr.yPos;
        instance.map = arr.map;
        // todo if set include the other values from server
        return instance;
    }
}
let targetList = [];
function getTargetTable() {
    document.getElementById("content").innerHTML = resourcePackage.templates["entrytableTempl.html"];
    sendAjax(null, "target", "getAll", printTargetTable);
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

function getEntryUpdatePopup() {
    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("content").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/EntryPopup.html", true);
    xhttp.send();
}

function getMapTable() {
    document.getElementById("content").innerHTML = resourcePackage.templates["mapTableTempl.html"];
}

let immage;

function previewFile() {
    let preview = document.getElementById("t_imgPreview"); //selects the query named preview
    let file = document.querySelector('input[type=file]').files[0]; //same as here
    let reader = new FileReader();

    reader.onloadend = function () {
        preview.src = reader.result;
        immage = preview.src;
    }

    if (file) {
        reader.readAsDataURL(file); //reads the data as a URL
    } else {
        preview.src = "";
    }
}

function sendAjax(object, subject, verb, callbackMethod) {

    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = callbackMethod;
    let objSend = "";
    if (object == null)
        object = "";

    if (subject == "target") {
        objSend = "&target=";
    } else if (subject == "user") {
        objSend = "&user=";
    } else {
        objSend = "&map="
    }
    let wwwForm =
        "username=" + username
        + "&passHash=" + passHash
        + "&subject=" + subject
        + "&verb=" + verb;
    if (verb != "getAll") {
        wwwForm += objSend + JSON.stringify(object);
    }
    let serverPage = 'ajax.php';
    xhttp.open("POST", serverPage, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); // One of the 2 possibilities for POST data to be transmitted via AJAX
    xhttp.send(wwwForm);
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
    closeUserUpdateWindow();
}

function closeUserUpdateWindow() {
    document.getElementById("userList").removeChild(document.getElementById("createUserWindow"))
}

let view = 0;

function switchView() {
    if (view == 0) {
        creatingCurrendtly = false;
        getMapTable();
        view = 1;
    }
    else {
        getEntryTable();
        view = 0;
    }
}

function test() {

    if (view == 0) {

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("entrylist").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "templates/Entry.html", true);
        xhttp.send();

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("userList").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "templates/User.html", true);
        xhttp.send();
    }
    else {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("mapList").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "templates/mapEntry.html", true);
        xhttp.send();
    }
}