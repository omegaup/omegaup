omegaup.OmegaUp.on('ready', function() {
  var courseAlias = /\/course\/([^\/]+)/.exec(window.location.pathname)[1];

  Promise.all([
    omegaup.API.Course.details({ alias: courseAlias }),
    omegaup.API.Course.myProgress({ alias: courseAlias }),
  ])
    .then(function([course, score]) {
      // Assignment lists by type.
      var assignments = {};
      for (var i = 0; i < course.assignments.length; ++i) {
        var type = course.assignments[i].assignment_type;
        if (!assignments.hasOwnProperty(type)) {
          assignments[type] = [];
        }
        assignments[type].push(course.assignments[i]);
        course.assignments[i].assignmentUrl =
          '/course/' +
          courseAlias +
          '/assignment/' +
          course.assignments[i].alias;
        course.assignments[i].scoreboardUrl =
          '/course/' +
          courseAlias +
          '/assignment/' +
          course.assignments[i].alias +
          '/scoreboard/' +
          course.assignments[i].scoreboard_url;
        course.assignments[i].scoreboardUrlAdmin =
          '/course/' +
          courseAlias +
          '/assignment/' +
          course.assignments[i].alias +
          '/scoreboard/' +
          course.assignments[i].scoreboard_url_admin;
        course.assignments[i].adminURL =
          '/course/' +
          courseAlias +
          '/assignment/' +
          course.assignments[i].alias +
          '/admin/#runs';
        course.assignments[i].startTime = omegaup.UI.formatDateTime(
          course.assignments[i].start_time,
        );
        course.assignments[i].finishTime = course.assignments[i].finish_time
          ? omegaup.UI.formatDateTime(course.assignments[i].finish_time)
          : omegaup.T.wordsUnlimitedDuration;

        var iScore = score.assignments[course.assignments[i].alias];
        var percent = (iScore.score / iScore.max_score) * 100;
        var percentText = iScore.max_score === 0 ? '--.--' : percent.toFixed(2);
        course.assignments[i].progress = percentText + '%';
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
    .catch(omegaup.UI.apiError);
});
