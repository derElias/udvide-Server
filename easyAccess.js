/**
 * Created by Elias on 31.05.2017.
 */
function searchForEntry(){

}

function deleteEntry() {

}

function createEntry() {
    getEntryUpdatePopup();

}

function updateEntry() {

}

function getPermissionList() {
        document.getElementById("Userpermissions").classList.toggle("show");
}

function setUserClient() {
    document.getElementById("permissionsUser").innerHTML = "Clien" ;
}

function setUserAdmin() {
    document.getElementById("permissionsUser").innerHTML = "Admin" ;
}

function setUserEditor() {
    document.getElementById("permissionsUser").innerHTML = "Editor" ;
}

function saveUser() {
    /*create or update the User with new Settings*/
}

function getEntryTable() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("content").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/entrytableTempl.html", true);
    xhttp.send();
}

function getEntryUpdatePopup() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("content").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/EntryPopup.html", true);
    xhttp.send();
}

function getHome() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("content").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/home.html", true);
    xhttp.send();
}

function getUserList() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("content").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/UserManagementTempl.html", true);
    xhttp.send();
}