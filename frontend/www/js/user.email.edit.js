$('document')
    .ready(function() {
      $('form#user_edit_email_form')
          .submit(function() {
            $('#wait').show();

            omegaup.API.User.updateMainEmail({email: $('#email').val()})
                .then(function(response) {
                  $('#status')
                      .text(omegaup.T.userEditSuccessfulEmailUpdate)
                      .addClass('alert-success')
                      .slideDown();
                })
                .fail(omegaup.UI.apiError)
                .always(function() { $('#wait')
                                         .hide(); });

            // Prevent page refresh on submit
            return false;
          });
    });
