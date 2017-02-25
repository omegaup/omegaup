$('document')
    .ready(function() {
      $('form#user_edit_email_form')
          .submit(function() {
            $('#wait').show();

            omegaup.API.User.updateMainEmail({email: $('#email').val()})
                .then(function(response) {
                  $('#status')
                      .html(
                          'Email actualizado correctamente! En unos minutos ' +
                          'recibirás más instrucciones en tu email. No ' +
                          'olvides revisar tu carpeta de Spam.');
                  $('#status').addClass('alert-success');
                  $('#status').slideDown();
                })
                .fail(omegaup.UI.apiError)
                .always(function() { $('#wait')
                                         .hide(); });

            // Prevent page refresh on submit
            return false;
          });
    });
