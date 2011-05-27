var DEBUG = true;

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




var LoginBar = function (){
	
	var loginStatus = false;
	
	this.setStatus = function ( st ){
		loginStatus = st;
		var html;
		if(loginStatus){
			html = "Bienvenido de regreso !";
		}else{
			html = "Hello stranger ! <a href='login.php'>Iniciar sesion con facebook</a>";			
		}
		
		$(".login_bar").html( html );
	}
	
	this.setUser = function ( usrData ){
		$(".login_bar").html( "Hola " + usrData.name + " ! <a href='login.php'>Cerrar sesion</a>" );
	}
	
	this.render = function (){
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
  *
  *
  **/
var lb = new LoginBar ();

$.ajaxSetup({
  	url: 'api.php',
	type: 'post'
});

