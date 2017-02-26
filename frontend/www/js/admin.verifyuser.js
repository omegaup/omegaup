omegaup.OmegaUp.on('ready', function() {
  omegaup.UI.userTypeahead($('#username'));
  $('#verify-user-form')
      .submit(function(ev) {
        ev.preventDefault();
        username = $('#username').val();

        omegaup.API.User.verifyEmail({usernameOrEmail: username})
            .then(function() {
              omegaup.UI.success('User successfully verified!');
              $('div.post.footer').show();
            })
            .fail(omegaup.UI.apiError);
      });
});
