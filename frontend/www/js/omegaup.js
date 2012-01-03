var DEBUG = true;


window.fbAsyncInit = function() {

  	FB.init({appId: '197705690257857', status: true, cookie: true, xfbml: true});
	
	if(DEBUG){
		console.log("Facebook loaded", FB);
	}
	
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
};



var loginWithFaceook = function(  ){
	if(DEBUG){
		console.log("facebook is back");
	}
	
	//test if he really logged in
	FB.getLoginStatus(function(response) {		
		if (response.status == "connected") {
			//get his information
			FB.api('/me', function(response) {

				console.log("back from fb query" , response);
				//send login to omegaup api
				
				$.ajax({
					url: "arena/fblogin/",
					type: "POST",
					data :{
						use_facebook : true,
						facebook_id : response.id,
						email		: response.email,
						name		: response.name
					},
					success: function( response ){
			 			//refresh the site
						console.log(response)
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
