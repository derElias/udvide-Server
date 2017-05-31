/**
 * Created by Elias on 31.05.2017.
 */
function searchForEntry(){

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
