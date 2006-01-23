function addEvent(obj, event, func, capture){
	if (obj.addEventListener){
		obj.addEventListener(event, func, capture);
	} else if (obj.attachEvent){
		obj.attachEvent("on"+event, func);
	}
}


function byId(id) {
	return document.getElementById(id);
}


function getHTTPObject() { 
	var xmlhttp;
	/*@cc_on
	  @if (@_jscript_version >= 5) try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP"); 
	  } 
	  catch (e) { 
	  try { xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); } 
	  catch (E) { xmlhttp = false; }
	  } 
	  @else 
	  xmlhttp = false;
	  @end @*/ 
	if (!xmlhttp && typeof XMLHttpRequest != 'undefined') { 
		try {
			xmlhttp = new XMLHttpRequest(); 
		}
		catch (e) {
			xmlhttp = false;
		} 
	}
	return xmlhttp; 
}

function ajaxRequest(url, callback) {
	var http = getHTTPObject();
	http.open("GET", url, true);
	http.onreadystatechange = function() {
		if(http.readyState == 4) callback();
	}
	http.send(null);
}


/* get, set, and delete cookies */
function getCookie( name ) {
	var start = document.cookie.indexOf( name + "=" );
	var len = start + name.length + 1;
	if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) ) {
		return null;
	}
	if ( start == -1 ) return null;
	var end = document.cookie.indexOf( ";", len );
	if ( end == -1 ) end = document.cookie.length;
	return unescape( document.cookie.substring( len, end ) );
}
	
function setCookie( name, value, expires, path, domain, secure ) {
	var today = new Date();
	today.setTime( today.getTime() );
	if ( expires ) {
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	document.cookie = name+"="+escape( value ) +
		( ( expires ) ? ";expires="+expires_date.toGMTString() : "" ) + //expires.toGMTString()
		( ( path ) ? ";path=" + path : "" ) +
		( ( domain ) ? ";domain=" + domain : "" ) +
		( ( secure ) ? ";secure" : "" );
}
	
function deleteCookie( name, path, domain ) {
	if ( getCookie( name ) ) document.cookie = name + "=" +
			( ( path ) ? ";path=" + path : "") +
			( ( domain ) ? ";domain=" + domain : "" ) +
			";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}

