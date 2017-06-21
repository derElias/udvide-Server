var resourcePackage;
var xhttp = new XMLHttpRequest();
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
        image: document.getElementById("t_imgPreview").src,
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

var username;
var passHash;
function login() {
    username=document.getElementById("login_username").value;
    passHash=document.getElementById("login_password").value;
    getEntryTable();
    loadHeader();
}

function loadFooter() {
    document.getElementById("footer").innerHTML = resourcePackage.templates["footer.html"]
}

function logout() {
    username="";
    passHash="";
    loadLogin();
    emptyHeader();
}
function loadHeader() {
    document.getElementById("header").innerHTML = resourcePackage.templates["headerContent.html"]
}

function emptyHeader() {
    document.getElementById("header").removeChild(document.getElementById("headerContent"))
}

var creatingCurrendtly= false;
function createUser() {
    if (creatingCurrendtly == false) {

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var newItem = document.createElement("div");
                newItem.setAttribute("id","createUserWindow")
                newItem.innerHTML = this.responseText;

                var list = document.getElementById("userList");
                list.insertBefore(newItem, list.childNodes[0]);
                creatingCurrendtly = true;
            }
        };
        xhttp.open("GET", "templates/createUser.html", true);
        xhttp.send();
    }
}


    function loadLogin() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("content").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "templates/login.html", true);
        xhttp.send();
    }

    //To set when choosing Map
    var x=null;
    var y=null;
    var map=null;

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

    function getEntryTable() {
        document.getElementById("content").innerHTML = resourcePackage.templates["entrytableTempl.html"];
        sendAjax(null, "target", "getAll")
    }

    function getEntryUpdatePopup() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("content").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "templates/EntryPopup.html", true);
        xhttp.send();
    }

    function getMapTable(){
        document.getElementById("content").innerHTML = resourcePackage.templates["mapTableTempl.html"];
    }

    var immage;

    function previewFile() {
        var preview = document.getElementById("t_imgPreview"); //selects the query named preview
        var file = document.querySelector('input[type=file]').files[0]; //same as here
        var reader = new FileReader();

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

    function sendAjax(object, subject, verb) {
  /*      let target = {
            id: document.getElementById("t_id").value,
            name: document.getElementById("t_name").value,
            image: image,
            activeFlag: document.getElementById("t_activeFlag").checked,
            xPos: document.getElementById("xPos").value,
            yPos: document.getElementById("yPos").value,
            map: document.getElementById("map").value,
            content: document.getElementById("t_content").value
        };
        let user = {
            passHash: getElementById("update_user_password").value,
            username: getElementById("update_user_name").value,
            role: getElementById("update_user_role").value,
            createTargetLimit: getElementById("update_user_tnumber").value,
        };
        let map = {
            name: getElementById("t_id").value,
            image: getElementById("t_id").value,
        };
*/
        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("test").innerHTML = this.responseText;
            }
        };
        var obSend;
        if(subject == "target"){
            obSend="&target=";
        }
        else{
            if(subject=="user"){
                obSend="$user=";
            }
            else{
                obSend="&map=";
            }
        }
        let wwwForm =
            "username=" + username
            + "&passHash=" + passHash
            + "&subject=" + subject
            + "&verb=" + verb
            + obSend + JSON.stringify(object);

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
            createTargetLimit:document. getElementById("update_user_tnumber").value,

        }
        sendAjax(user, user.username, "create");
        creatingCurrendtly=false;
        closeUserUpdateWindow();
    }
    
    function closeUserUpdateWindow() {
        document.getElementById("userList").removeChild(document.getElementById("createUserWindow"))
    }

    var view = 0;

    function switchView() {
        if (view == 0) {
            creatingCurrendtly=false;
            getMapTable();
            view = 1;
        }
        else {
            getEntryTable();
            view = 0;
        }
    }

    function test() {

        if(view==0) {

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
        else{
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