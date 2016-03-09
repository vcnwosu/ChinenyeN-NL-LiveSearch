// search bar element
var sb = document.getElementById("search-bar");

// just to keep from unnecessary ajax calls
var oldSB = "";

var noSearch = "<section class=\"result\"><header>Welcome. Enter Your Search</header></section>";

// set up timing for search bar
setInterval(checkValue, 250);

// to check the difference between old and new input
function checkValue() {
    // validate input first
    // check for space or backspace or delete
    var code = sb.value.charCodeAt(sb.value.length - 1);

    if(code === 32 || code === 8 || code === 127) {
        return;
    }

    // make sure not to send empty strings
    if(sb.value === "") {
	document.getElementsByTagName("main")[0].innerHTML = noSearch;
        return;
    }

    // check to make sure oldSB and sb are not the same value
    if(oldSB !== sb.value) {
        oldSB = sb.value;
        console.log(oldSB); // write to console in case of debug
        runAJAX(oldSB); // run ajax
    }
}

// to take string from input and initiate AJAX request
// place contents within the innerHTML of the <main></main> element
function runAJAX(text) {

    // create new XMLHttpRequest object
    var AJAXReq = new XMLHttpRequest();

    // open AJAX connection
    AJAXReq.open("GET", "php/searchengine.php?q=" + text, true);

    // prep AJAX object to receive information
    AJAXReq.onreadystatechange = function() {

        // check the estate of the ajax request and server status
        if(AJAXReq.readyState == 4 && AJAXReq.status == 200) {
            // populate the <main></main> element with response text
            document.getElementsByTagName("main")[0].innerHTML = AJAXReq.responseText;
        }

    }

    // send the request
    AJAXReq.send();

}
