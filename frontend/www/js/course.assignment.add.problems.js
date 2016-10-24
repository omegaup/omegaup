$('document')
    .ready(function() {
      $('.assignment-add-problem')
          .submit(function() {
            var courseAlias = /\/course\/([^\/]+)\/edit\/?.*/.exec(
                window.location.pathname)[1];

            omegaup.API.addCourseAssignmentProblem(
                courseAlias,
                $('.assignment-add-problem #assignments-list').val(),
                $('.assignment-add-problem #problems-dropdown').val(),
                function(data) {
                  if (data.status == 'ok') {
                    omegaup.UI.success(
                        omegaup.T['courseAssignmentProblemAdded']);
                  } else {
                    omegaup.UI.error(data.error || 'error');
                  }
                });

            return false;
          });
    });
