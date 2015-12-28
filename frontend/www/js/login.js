$(document).ready(function() {
	function registerAndLogin(){
		if ($('#reg_pass').val() != $('#reg_pass2').val()) {
			OmegaUp.ui.error(OmegaUp.T.loginPasswordNotEqual);
			return false;
		}

		if ($('#reg_pass').val().length < 8) {
			OmegaUp.ui.error(OmegaUp.T.loginPasswordTooShort);
			return false;
		}

		if (grecaptcha.getResponse().length == 0) {
			OmegaUp.ui.error(OmegaUp.T.unableToVerifyCaptcha);
			return false;
		}

		omegaup.createUser(
			$('#reg_email').val(),
			$('#reg_username').val(),
			$('#reg_pass').val(),
			grecaptcha.getResponse(),
			function (data) {
				//registration callback
				if (data.status != 'ok') {
					OmegaUp.ui.error(data.error);
				} else {
					$("#user").val($('#reg_email').val());
					$("#pass").val($('#reg_pass').val());
					$("#login_form").submit();
				}
			}
		);
		return false; // Prevent form submission
	}

	$("#register-form").submit(registerAndLogin);
});

var logmeoutOnce = window.location.href.endsWith("?logout");

function signInCallback(authResult) {
	//$('#google-signin').attr('style', 'display: none');
	if (logmeoutOnce) {
		gapi.auth.signOut();
		logmeoutOnce = false;
	} else if (authResult['code']) {
		omegaup.googleLogin(
				authResult['code'],
				function (data) {
					if (data.status == 'ok') {
						window.location.reload();
					} else {
						OmegaUp.ui.error(data.error);
					}
				});
	} else if (authResult['error']) {
		// Esto se hace en cada refresh a la pagina de login.
		//OmegaUp.ui.error('There was an error: ' + authResult['error']);
	}
}

// https://developers.google.com/+/web/signin/server-side-flow
function renderButton() {
	gapi.signin2.render('google-signin', {
		'scope': 'email',
		'width': 200,
		'height': 50,
		'longtitle': false,
		"redirect_uri" : "postmessage",
	});
}

