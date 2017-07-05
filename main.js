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
    if (this.readyState === 4 && this.status === 200) {
        let response = JSON.parse(this.responseText);
        if (response.success === true) {
            initialRead();
        }
        else {
            printLoginFail();
        }
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
    emptyStorage();
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

function testSuccessful(){
    if (this.readyState === 4 && this.status === 200) {
        let response = JSON.parse(this.responseText);

        console.log("testresponse:"+response);

        if (response.success === false) {
            alert("action: " + verb + " unssuccessfull")
        }
        else {
            verb=null;
        }
    }
}