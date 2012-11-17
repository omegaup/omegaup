var DEBUG = true;


function OmegaUp( ) {
	var self = this;
	this.username = null;
	this.deltaTime = 0;
}



/*
OmegaUp.prototype.CreateUser = function(s_Email, s_Username, s_PlainPassword, callback) {
	$.post(
		'/api/user/create',
		{ s_Email: s_Email, s_Username: s_Username, s_PlainPassword : s_PlainPassword },
		function (data) {
			callback(data);
		},
		'json'
	);
};
*/
OmegaUp.UI = {
	Error : function ( reason ){
		$.msgBox({
		    title: "Error",
		    content: reason,
		    type: "error",
		    showButtons: false,
		    opacity: 0.9,
		    autoClose:false
		});
	}
}

$(document).ajaxError(function(e, xhr, settings, exception) {
	var errorToUser = "Unknown error.";
	try{
		var response = jQuery.parseJSON(xhr.responseText);
		errorToUser = response.error;
	}catch(e){
		
	}

	OmegaUp.UI.Error( errorToUser );
});


OmegaUp.prototype.CreateUser = function(s_Email, s_Username, s_PlainPassword, callback) {
	console.log("Creating user");
	$.post(
		'/api/user/create/email/' + s_Email + "/username" + s_Username + "/password/" + s_PlainPassword ,
		{ email: s_Email, username: s_Username, password : s_PlainPassword },
		function (data) {
			
			console.log("returned", data);

			if( data.status !== undefined && data.status == "error")
			{
				OmegaUp.UI.Error( data.error );
			}else{
				if(callback !== undefined){ callback( data ) }
			}
			
			
		},
		'json'
	);
};

var omega = new OmegaUp( );

