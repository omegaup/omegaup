$('#forgot-password-form')
    .submit(function(ev) {
      ev.preventDefault();
      $('#submit').prop('disabled', true);
      omegaup.UI.dismissNotifications();
      omegaup.API.Reset.create({email: $('#email').val()})
          .then(function() { omegaup.UI.success(data.message); })
          .fail(omegaup.UI.apiError)
          .always(function() { $('#submit')
                                   .prop('disabled', false); });
      return false;
    });

$('#reset-password-form')
    .submit(function(ev) {
      ev.preventDefault();
      $('#submit').prop('disabled', true);
      omegaup.UI.dismissNotifications();
      omegaup.API.Reset.update({
                         email: $('#email').val(),
                         reset_token: $('#reset_token').val(),
                         password: $('#password').val(),
                         password_confirmation:
                             $('#password_confirmation').val()
                       })
          .then(function() { omegaup.UI.success(data.message); })
          .fail(omegaup.UI.apiError)
          .always(function() { $('#submit')
                                   .prop('disabled', false); });
    });
