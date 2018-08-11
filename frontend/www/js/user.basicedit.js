$('document')
    .ready(function() {
      omegaup.API.User.profile()
          .then(function(data) {
            $('#basic-username').val(data.userinfo.username);
          })
          .fail(omegaup.UI.apiError);

      $('form#add-password-form')
          .on('submit', function(ev) {
            ev.preventDefault();
            if ($('#new-password-1').val() != $('#new-password-2').val()) {
              omegaup.UI.error(T.passwordMismatch);
              return false;
            }

            omegaup.API.User.updateBasicInfo({
                              username: $('#basic-username').val(),
                              password: $('#new-password-1').val(),
                            })
                .then(function(response) { window.location = '/profile/'; })
                .fail(omegaup.UI.apiError);
            return false;  // Prevent page refresh on submit
          });
    });
