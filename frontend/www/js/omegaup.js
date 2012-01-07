var DEBUG = true;


window.fbAsyncInit = function() {

  	FB.init({appId: '197705690257857', status: true, cookie: true, xfbml: true});
	
	if(DEBUG){
		console.log("Facebook loaded", FB);
	}
	
	/*
	FB.getLoginStatus(function(response) {		
		if (response.status == "connected") {
			//usuario conectado, buscar informacion
			FB.api('/me', function(response) {
				//arrived
				console.log(response);
				//send login to omegaup api
			});
	  	} else {
			console.log("ntli");
	  	}
	});
	*/
};



var loginWithFaceook = function(  ){
	
	
	//test if he really logged in
	FB.getLoginStatus(function(response) {	
		if(DEBUG){
			console.log("facebook is back");
		}
		if (response.status == "connected") {
			//get his information
			FB.api('/me', function(response) {

				console.log("back from fb query" , response);
				//send login to omegaup api
				
				$.ajax({
					url: "arena/fblogin/",
					type: "POST",
					data :{
						facebook_id : response.id,
						email		: response.email,
						name		: response.name
					},
					success: function( response ){
			 			//refresh the site
						location.reload(true);
					}
				});
				
			});
	  	} else {
			//he didnt really log in somehow
			if(DEBUG){
				console.log("Not Logged In");
			}
			//show error perhaps?

	  	}
	});
	
	//send login to omegaup

}





var Contests = 
{
	
	new_contest : function(   )
	{
		
	},

	test_contest_valid_params : function (    )
	{
		
	}


}



var ApiCall = 
{
	
	POST : function ()
	{
		
	},

	GET : function ()
	{
		
	}

};



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
	
	

});



var fb_login_n = 99;
function fb_login_test(){
	fb_login_n++;
	$.ajax({
						url: "arena/fblogin/",
						type: "POST",
						data :{
							facebook_id : "1792813" + fb_login_n ,
							email		: "fb"+fb_login_n+"@itca.1com1",
							name		: "alan gonzalez"
						},
						success: function( response ){
				 			//refresh the site
							console.log(response)
						}
					});
}