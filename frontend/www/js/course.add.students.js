$('document')
    .ready(function() {
      $('#add-member-form')
          .submit(function() {
            var courseAlias = /\/course\/([^\/]+)\/edit\/?.*/.exec(
                window.location.pathname)[1];

            omegaup.API.addStudentToCourse({
                         course_alias: courseAlias,
                         username: $('#member-username').val()
                       })
                .then(function(data) {
                  if (data.status == 'ok') {
                    omegaup.UI.success(omegaup.T.courseStudentAdded);
                  } else {
                    omegaup.UI.error(data.error || 'error');
                  }
                });
            refreshStudentList();
            return false;
          });
    });
