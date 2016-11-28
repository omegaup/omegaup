omegaup.OmegaUp.on('ready', function() {
      $('.new_course_assignment_form')
          .submit(function() {
            var courseAlias = /\/course\/([^\/]+)\/edit\/?.*/.exec(
                window.location.pathname)[1];

            omegaup.API.createCourseAssignment(
                courseAlias, $('.new_course_assignment_form #title').val(),
                $('.new_course_assignment_form #description').val(),
                (new Date($('.new_course_assignment_form #start_time').val())
                     .getTime()) /
                    1000,
                (new Date($('.new_course_assignment_form #finish_time').val())
                     .getTime()) /
                    1000,
                $('.new_course_assignment_form #alias').val(),
                $('.new_course_assignment_form #assignment_type').val(),
                function(data) {
                  if (data.status == 'ok') {
                    omegaup.UI.success(omegaup.T['courseAssignmentAdded']);
                    $('.new_course_assignment_form')[0].reset();
                  } else {
                    omegaup.UI.error(data.error || 'error');
                  }
                });

            return false;
          });
    });
