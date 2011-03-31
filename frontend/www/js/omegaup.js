var DEBUG = true;

window.fbAsyncInit = function() {

  	FB.init({appId: '197705690257857', status: true, cookie: true, xfbml: true});
	
	if(DEBUG){
		console.log("Facebook loaded");
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
	
}


var lb = new LoginBar ();


