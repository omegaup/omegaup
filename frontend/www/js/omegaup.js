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
		loginStatus = st;
		var html;
		if(loginStatus){
			html = "Bienvenido de regreso !";
		}else{
			html = "Hello stranger ! <a href='login.php'>Iniciar sesion</a>";			
		}
		
		$(".login_bar").html( html );
	}
	
	this.setUser = function ( usrData ){
		$(".login_bar").html( "Hola " + usrData.name + " ! <a href='login.php'>Cerrar sesion</a>" );
	}
	
	this.render = function (){
		//asume not valid user
		this.setStatus ( false );
		$(".login_bar").slideDown()
	}
	
};



var Registry = function(){
	
	this.validate_basic_user_registration = function(name, email, password){
		
		if(name === undefined){
			return {
				valid: false,
				reason : "no name"
			}
		}
		
		if(email === undefined){
			return {
				valid: false,
				reason : "no email"
			}
		}
		
		if(password === undefined){
			return {
				valid: false,
				reason : "no pass"
			}
		}
		
		if(name.length < 5){
			return {
				valid : false,
				reason : "Tu nombre es muy corto."
			}
		}
		
		
		var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,4}$/; 
		
		if( !emailPattern.test(email) ){
			return {
				valid : false,
				reason : "Tu email no es valido"
			}
		}
	
		if(password.length < 5){
			return {
				valid : false,
				reason : "Tu pass es muy corto."
			}
		}
	
		return { valid : true };
	};
	
	
	this.send_basic_registration = function(name, email, password, callback){
		 $.ajax({ 
			data: {
				action : "new_user_basic",
				name : name,
				email : email,
				password : password
			},
			success : function(r ){
				callback.call(null, $.parseJSON( r ));
			}
		});
	};
};








/**
  * create login bar
  *
  **/
var lb = new LoginBar ();

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
