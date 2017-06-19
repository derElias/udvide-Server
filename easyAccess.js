function searchForEntry(){

}

function deleteEntry() {

}

function createEntry() {
    getEntryUpdatePopup();

}

function updateEntry() {

}

function createUser() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("content").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/CreateUser.html", true);
    xhttp.send();
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
    /* TODO create or update the User with new Settings*/
}

function saveEntry() {
    var target = JSON.stringify({});
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("content").innerHTML = this.responseText;
        }
    };
    xhttp.open("POST", "ajax.php?subject=target&verb=" + method + target + true);
    xhttp.send();
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

function getMapTable() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("content").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "templates/mapTableTempl.html", true);
        xhttp.send();
}


function previewFile(){
    var preview = document.getElementById("preview"); //selects the query named preview
    var file    = document.querySelector('input[type=file]').files[0]; //same as here
    var reader  = new FileReader();

    reader.onloadend = function () {
        preview.src = reader.result;
    }

    if (file) {
        reader.readAsDataURL(file); //reads the data as a URL
    } else {
        preview.src = "";
    }
}

var view = 0;
function switchView() {
    if(view == 0){
       getMapTable();
       view=1;
    }
    else
    {
        getEntryTable();
        view=0;
    }
}

function test() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("entrylist").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/Entry.html", true);
    xhttp.send();

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("userlist").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "templates/User.html", true);
    xhttp.send();
}