omegaup.OmegaUp.on('ready', function() {
  var courseAlias = /\/course\/([^\/]+)/.exec(window.location.pathname)[1];
  omegaup.API.Course.details({alias: courseAlias})
      .then(function(course) {
        // Assignment lists by type.
        var assignments = {};
        for (var i = 0; i < course.assignments.length; ++i) {
          var type = course.assignments[i].assignment_type;
          if (!assignments.hasOwnProperty(type)) {
            assignments[type] = [];
          }
          assignments[type].push(course.assignments[i]);
          course.assignments[i].assignmentUrl = '/course/' + courseAlias +
                                                '/assignment/' +
                                                course.assignments[i].alias;
          course.assignments[i].startTime = omegaup.UI.formatDateTime(
              new Date(1000 * course.assignments[i].start_time));
          course.assignments[i].finishTime = omegaup.UI.formatDateTime(
              new Date(1000 * course.assignments[i].finish_time));
        }

        // Put assignment lists back in a separate field per type.
        for (var type in assignments) {
          course[type] = assignments[type];
        }

        course.isAdmin = course.is_admin;
        course.addAssignmentUrl =
            '/course/' + courseAlias + '/edit/#assignments/new/';
        course.editUrl = '/course/' + courseAlias + '/edit/';
        course.scoreboardUrl = '/course/' + courseAlias + '/students/';
        course.addStudentsUrl = '/course/' + courseAlias + '/edit/#students';

        ko.applyBindings(course, $('#course-info')[0]);
      })
      .fail(omegaup.UI.apiError);
});
