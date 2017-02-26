$('document')
    .ready(function() {
      omegaup.API.User.profile()
          .then(function(data) {
            $('#username').val(data.userinfo.username);
            $('#name').val(data.userinfo.name);
          })
          .fail(omegaup.UI.apiError);

      var formSubmit = function() {
        var newPassword = $('#new-password-1').val();
        var newPassword2 = $('#new-password-2').val();
        if (newPassword != newPassword2) {
          omegaup.UI.error('Los passwords nuevos deben ser iguales.');
          return false;
        }

        omegaup.API.User.updateBasicInfo({
                          username: $('#username').val(),
                          name: $('#name').val(),
                          password: $('#new-password-1').val(),
                        })
            .then(function(response) { window.location = '/profile/'; })
            .fail(omegaup.UI.apiError);
        return false;  // Prevent page refresh on submit
      };

      $('form#user_profile_form').submit(formSubmit);
    });
