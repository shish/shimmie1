var defaultTexts = new Array();

window.onload = function(e) {
	var sections=get_sections();
	for(var i=0;i<sections.length;i++) toggle(sections[i]);

	initAjax("searchBox", "search_completions");
	initAjax("tagBox", "tag_completions");
	initGray("searchBox", "Search");
	initGray("commentBox", "Comment");
	initGray("tagBox", "tagme");
}


function endWord(sentance) {
	words = sentance.split(" ");
	return words[words.length-1];
}

function initAjax(boxname, areaname) {
	var box = byId(boxname);
	if(!box) return;

	addEvent(
		box,
		"keyup", 
		function f() {
			ajaxRequest(
				"ajax.php?start="+endWord(box.value), 
				function g(text) {byId(areaname).innerHTML = text;}
			);
		},
		false
	);
	
}

function initGray(boxname, text) {
	var box = byId(boxname);
	if(!box) return;

	addEvent(box, "focus", function f() {cleargray(box, text);}, false);
	addEvent(box, "blur",  function f() {setgray(box, text);}, false);

	if(box.value == text) {
		box.style.color = "#999";
		box.style.textAlign = "center";
	}
	else {
		box.style.color = "#000";
		box.style.textAlign = "left";
	}
}

function cleargray(box, text) {
	if(box.value == text) {
		box.value = "";
		box.style.color = "#000";
		box.style.textAlign = "left";
	}
}
function setgray(box, text) {
	if(box.value == "") {
		box.style.textAlign = "center";
		box.style.color = "gray";
		box.value = text;
	}
}

function toggleLogin(checkbox, button) {
	if(checkbox.checked) {
		button.value = "Create Account";
	}
	else {
		button.value = "Log In";
	}
}

function scale(img) {
	if(img.style.width != "90%") img.style.width = "90%";
	else img.style.width = null;
}

function showUp(elem) {
	e = document.getElementById(elem)
	if(!e) return;
	e.style.display = "";
//	alert(e.type+": "+e.value);
	if(e.value.match(/^http|^ftp/)) {
		e.type = "text";
		alert("Box is web upload");
	}
}


