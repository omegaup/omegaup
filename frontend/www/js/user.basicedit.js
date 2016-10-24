$('document')
    .ready(function() {
      omegaup.API.getProfile(null, function(data) {
        $('#username').val(data.userinfo.username);
        $('#name').val(data.userinfo.name);
      });

      var formSubmit = function() {
        var newPassword = $('#new-password-1').val();
        var newPassword2 = $('#new-password-2').val();
        if (newPassword != newPassword2) {
          omegaup.UI.error('Los passwords nuevos deben ser iguales.');
          return false;
        }

        omegaup.API.updateBasicProfile(
            $('#username').val(), $('#name').val(), $('#new-password-1').val(),
            function(response) {
              if (response.status == 'ok') {
                window.location = '/profile/';
                return false;
              } else if (response.error !== undefined) {
                omegaup.UI.error(response.error);
              }
            });
        return false;  // Prevent page refresh on submit
      };

      $('form#user_profile_form').submit(formSubmit);
    });
