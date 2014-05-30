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

		omegaup.createUser(
			$('#reg_email').val(),
			$('#reg_username').val(),
			$('#reg_pass').val(),
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
