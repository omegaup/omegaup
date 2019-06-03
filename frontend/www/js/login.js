omegaup.OmegaUp.on('ready', function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);

  function registerAndLogin(ev) {
    ev.preventDefault();

    if ($('#reg_pass').val() != $('#reg_pass2').val()) {
      omegaup.UI.error(omegaup.T.passwordMismatch);
      return false;
    }

    if ($('#reg_pass').val().length < 8) {
      omegaup.UI.error(omegaup.T.loginPasswordTooShort);
      return false;
    }

    var recaptchaResponse = null;
    if (payload.validateRecaptcha) {
      if (typeof(grecaptcha) === 'undefined' ||
          grecaptcha.getResponse().length == 0) {
        omegaup.UI.error(omegaup.T.unableToVerifyCaptcha);
        return false;
      }
      recaptchaResponse = grecaptcha.getResponse();
    }

    omegaup.API.User.create({
                      email: $('#reg_email').val(),
                      username: $('#reg_username').val(),
                      password: $('#reg_pass').val(),
                      recaptcha: recaptchaResponse,
                    })
        .then(function(data) {
          // registration callback
          $('#user').val($('#reg_email').val());
          $('#pass').val($('#reg_pass').val());
          $('#login_form').trigger('submit');
        })
        .fail(omegaup.UI.apiError);
    return false;  // Prevent form submission
  }

  $('#register-form').on('submit', registerAndLogin);
});

function signInCallback(googleUser) {
  // Only log in if the user actually clicked the sign-in button.
  omegaup.API.Session.googleLogin(
                         {storeToken: googleUser.getAuthResponse().id_token})
      .then(function(data) { window.location.reload(); })
      .fail(omegaup.UI.apiError);
}

function signInFailure(error) {
  console.error(error);
}

// https://developers.google.com/+/web/signin/server-side-flow
function renderButton() {
  gapi.signin2.render('google-signin', {
    'scope': 'email',
    'theme': 'dark',
    'longtitle': false,
    'onsuccess': signInCallback,
    'onfailure': signInFailure,
  });
}
