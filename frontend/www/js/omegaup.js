var DEBUG = true;


window.fbAsyncInit = function() {

  	FB.init({appId: '197705690257857', status: true, cookie: true, xfbml: true});
	
	if(DEBUG){
		console.log("Facebook loaded", FB);
	}
	
	FB.getLoginStatus(function(response) {		
		if (response.session) {
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
		if (response.session) {
			//get his information
			FB.api('/me', function(response) {

				console.log("back from fb query" , response);
				//send login to omegaup api
				
				$.ajax({
					url: "arena/login/",
					params :{
						use_facebook : true,
						facebook_id : "32984329784",
						email		: "alskdjflasdfj"
					}
					success: function( response ){
			 			//refresh the site
						console.log(response)
					}
				});
				
			});
	  	} else {
			//he didnt really log in somehow
			console.log("ntli");
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
