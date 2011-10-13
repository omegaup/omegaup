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
