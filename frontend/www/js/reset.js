$('#forgot-password-form').on('submit', function(ev) {
  ev.preventDefault();
  $('#submit').prop('disabled', true);
  omegaup.UI.dismissNotifications();
  omegaup.API.Reset.create({ email: $('#email').val() })
    .then(function(data) {
      omegaup.UI.success(data.message);
    })
    .catch(omegaup.UI.apiError)
    .finally(function() {
      $('#submit').prop('disabled', false);
    });
  return false;
});

$('#reset-password-form').on('submit', function(ev) {
  ev.preventDefault();
  $('#submit').prop('disabled', true);
  omegaup.UI.dismissNotifications();
  omegaup.API.Reset.update({
    email: $('#email').val(),
    reset_token: $('#reset_token').val(),
    password: $('#password').val(),
    password_confirmation: $('#password_confirmation').val(),
  })
    .then(function(data) {
      omegaup.UI.success(data.message);
    })
    .catch(omegaup.UI.apiError)
    .finally(function() {
      $('#submit').prop('disabled', false);
    });
});
