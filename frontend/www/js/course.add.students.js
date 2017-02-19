omegaup.OmegaUp.on('ready', function() {
  $('#add-member-form')
      .submit(function(ev) {
        ev.preventDefault();
        var courseAlias =
            /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

        omegaup.API.Course.addStudent({
                            course_alias: courseAlias,
                            usernameOrEmail: $('#member-username').val()
                          })
            .then(function(data) {
              refreshStudentList();
              omegaup.UI.success(omegaup.T.courseStudentAdded);
            })
            .fail(function(data) { omegaup.UI.error(data.error); });
      });
});
