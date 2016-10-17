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
