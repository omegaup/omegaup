$('document')
    .ready(function() {
      $('#username')
          .typeahead(
              {
                minLength: 2,
                highlight: true,
              },
              {
                source: omegaup.UI.typeaheadWrapper(omegaup.API.searchUsers),
                displayKey: 'label',
              })
          .on('typeahead:selected', function(item, val, text) {
            $('#username').val(val.label);
          });

      $('#change-password-form')
          .submit(function() {
            password = $('#password').val();
            username = $('#user').val();

            omegaup.API.forceChangePassword(
                username, password, function(response) {
                  if (response.status == 'ok') {
                    omegaup.UI.success('Password successfully changed!');
                    $('div.post.footer').show();
                  } else {
                    omegaup.UI.error(response.error || 'error');
                  }
                });
            return false;  // Prevent refresh
          });
    });
