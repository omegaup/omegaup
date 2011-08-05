var DEBUG = true;

/*
	//Fuck Facebook connect for now
	//since it need to be run on the
	//registered domain
window.fbAsyncInit = function() {

  	FB.init({appId: '197705690257857', status: true, cookie: true, xfbml: true});
	
	if(DEBUG){
		console.log("Facebook loaded", FB);
	}
	
	FB.getLoginStatus(function(response) {

		lb.setStatus( response.session );
		
		if (response.session) {
			//usuario conectado, buscar informacion
			FB.api('/me', function(response) {
				//arrived
				lb.setUser( response );
				lb.render();
			});
	  	} else {
			lb.render();
	  	}

	});
};
*/



var LoginBar = function (){
	
	if(DEBUG){
		console.log("Creating login bar");
	}
	
	var loginStatus = false;
	
	this.setStatus = function ( st ){
		if(DEBUG){ console.log("Setting login status to "+ st +"!"); }
		loginStatus = st;
		var html;
		if(loginStatus){
			html = "Bienvenido de regreso !";
		}else{
			html = "Hello stranger ! <a href='login.php'>Iniciar sesion</a>";			
		}
		
		$(".login_bar").html( html );
	}
	
	this.setUser = function ( usrName ){
		$(".login_bar").html( "Hola " + usrName + " ! <a href='login.php?out=1'>Cerrar sesion</a>" );
	}
	
	this.render = function (){
		//asume not valid user
		$(".login_bar").slideDown()
	}
	
};









/**
  * create login bar
  *
  **/
var lb = new LoginBar (   );




/**
  * Default ajax shit
  *
  **/
$.ajaxSetup({
  	url: 'api.php',
	type: 'post'
});






/*  ***************************** ***************************** *****************************
										OMEGA UP START 
    ***************************** ***************************** ***************************** */

/**
  *
  *  ON READY FOR JQUERY
  **/
$(document).ready(function() {
  	if(DEBUG){
		console.log("JS loaded ! Starting app now...");
	}
	
	lb.render();

});
