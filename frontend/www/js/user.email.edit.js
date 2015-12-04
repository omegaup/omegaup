$('document').ready(function() {
	$('form#user_edit_email_form').submit(function (){
		$('#wait').show();

		omegaup.updateMainEmail($('#email').val(), function (response) {
			if (response.status == "ok") {
				$('#status').html("Email actualizado correctamente! En unos minutos recibirás más instrucciones en tu email. No olvides revisar tu carpeta de Spam.");
				$('#status').addClass("alert-success");
				$('#status').slideDown();

				$('#wait').hide();
				return false;
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}

			$('#wait').hide();
		});

		// Prevent page refresh on submit
		return false;
	});
});
