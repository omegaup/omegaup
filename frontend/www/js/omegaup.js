window.fbAsyncInit = function() {
  	FB.init({appId: '197705690257857', status: true, cookie: true, xfbml: true});
	$(".login_bar").slideDown()
	console.log("facebook loaded");
	FB.getLoginStatus(function(response) {
	  if (response.session) {
	    alert("si")// logged in and connected user, someone you know
	  } else {
	    alert("no")// no user session available, someone you dont know
	  }
	});
};
//load javascript
/*
(function() {
  var e = document.createElement('script');
  e.type = 'text/javascript';
  e.src = document.location.protocol +
    '//connect.facebook.net/en_US/all.js ';
  e.async = true;
  document.getElementById('fb-root').appendChild(e);
}());
*/


$(function(){

	//login status


});
