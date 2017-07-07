//initial Request
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
        resourcePackage = JSON.parse(this.responseText);
        main();
    }
};
xhttp.open("POST", "resourcePackage.php", true);
xhttp.send();

//	calls loadLogin() and loadFooter
function main() {
    loadLogin();
    loadFooter();
}

//sets username and passHash in local storage and sends a Ajax request with login data and the callbackmehtod login()
function sendLoginData() {
    username = document.getElementById("login_username").value;
    passHash = document.getElementById("login_password").value;
    sendAjax(null, "user", "readAll",login);
}

//interprets the response from the Ajax request and calls initialRead() when login was sucessfull
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

//calls sendLoginData() when enter key is pressed
function onEnterLogin(e) {
    if (e.keyCode === 13) {
        sendLoginData();
    }
}

//calls loadLogin(), emptyHeader()and emptyStorage()
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

function roleToString(role) {
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