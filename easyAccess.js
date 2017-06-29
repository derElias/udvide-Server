let resourcePackage;
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
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


function sendLoginData() {
    username = document.getElementById("login_username").value;
    passHash = document.getElementById("login_password").value;
    sendAjax(null, "user", "readAll",login);
}

function login(){
    let response = JSON.parse(this.responseText);
    if (response.success === true) {
        userList = response.payLoad;
        loadUserAndTargetTable();
        loadHeader();
    }
    else {
        printLoginFail();
    }
}

function onEnterLogin(e) {
    if (e.keyCode === 13) {
        sendLoginData();
    }
}

function logout() {
    loadLogin();
    emptyHeader();
    emptySorage();
}

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
    if (verb !== "readAll") {
        wwwForm += objSend + JSON.stringify(object);
    }
    let serverPage = 'ajax.php';
    xhttp.open("POST", serverPage, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); // One of the 2 possibilities for POST data to be transmitted via AJAX
    xhttp.send(wwwForm);
}


function testSuccessful(){
    if (this.readyState === 4 && this.status === 200) {
        let response = JSON.parse(this.responseText);

        if (response.success === false) {
            alert("action: " + verb + " unssuccessfull")
        }
        else {
            verb=null;
        }
    }
}