omegaup.OmegaUp.on('ready', function() {
  omegaup.UI.userTypeahead($('#username'));
  $('#change-password-form')
      .submit(function(ev) {
        ev.preventDefault();
        omegaup.API.User.changePassword({
                          username: $('#user').val(),
                          password: $('#password').val()
                        })
            .then(function() {
              omegaup.UI.success('Password successfully changed!');
              $('div.post.footer').show();
            })
            .fail(omegaup.UI.apiError);
      });
});
