jQuery(document).ready(function() {
	function setCookie(name,value,days) {
	    var expires = "";
	    if (days) {
	        var date = new Date();
	        date.setTime(date.getTime() + (days*24*60*60*1000));
	        expires = "; expires=" + date.toUTCString();
	    }
	    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
	}
	function getCookie(name) {
	    var nameEQ = name + "=";
	    var ca = document.cookie.split(';');
	    for(var i=0;i < ca.length;i++) {
	        var c = ca[i];
	        while (c.charAt(0)==' ') c = c.substring(1,c.length);
	        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	    }
	    return null;
	}
	function eraseCookie(name) {   
	    document.cookie = name+'=; Max-Age=-99999999;';  
	}
	var wpdailp = getCookie('wpd-ai-landing');
	var wpdairs = getCookie('wpd-ai-referral');
	if ( wpdailp == null ) {
		url = window.location.href;
		setCookie('wpd-ai-landing', url, 3);
		console.log(getCookie('wpd-ai-landing'));
	}
	if ( wpdairs == null ) {
		ref = document.referrer;
		setCookie('wpd-ai-referral', ref, 3);
		console.log(getCookie('wpd-ai-referral'));
	}
});