$('document')
    .ready(function() {
      omeguap.UI.userTypeahead($('#username'));
      $('#verify-user-form')
          .submit(function() {
            username = $('#username').val();

            omegaup.API.forceVerifyEmail(username, function(response) {
              if (response.status == 'ok') {
                omegaup.UI.success('User successfully verified!');
                $('div.post.footer').show();
              } else {
                omegaup.UI.error(response.error || 'error');
              }
            });
            return false;  // Prevent refresh
          });
    });
