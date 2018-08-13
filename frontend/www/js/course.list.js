omegaup.OmegaUp.on('ready', function() {
  function updateViewModel(model, data, section) {
    var current = model[section + 'CoursesCurrent'];
    var past = model[section + 'CoursesPast'];
    for (var i = 0; i < data[section].length; ++i) {
      var course = data[section][i];
      course.courseURL = '/course/' + course.alias;
      course.startDate = omegaup.UI.formatDate(course.start_time);
      course.endDate = omegaup.UI.formatDate(course.finish_time);
      course.numHomeworks = course.counts.homework;
      course.numTests = course.counts.test;
      course.activityURL = '/course/' + course.alias + '/activity/';
      course.submissionsListUrl = '/course/' + course.alias + '/list/';
      course.submissionsList = omegaup.T.courseListSubmissionsByGroup;
      course.activity = omegaup.T.wordsActivityReport;
      if (course.finish_time > Date.now()) {
        current.push(course);
      } else {
        past.push(course);
      }
    }
  }
  omegaup.API.Course.listCourses()
      .then(function(data) {
        if (data.status != 'ok') {
          omegaup.UI.error(data.error);
          return;
        }
        var viewModel = {
          adminCoursesCurrent: ko.observableArray(),
          adminCoursesPast: ko.observableArray(),
          studentCoursesCurrent: ko.observableArray(),
          studentCoursesPast: ko.observableArray(),
        };

        updateViewModel(viewModel, data, 'admin');
        updateViewModel(viewModel, data, 'student');
        ko.applyBindings(viewModel);

        // Enable the first visible tab.
        var tabs = $('.nav-link');
        if (tabs.length > 0) {
          $(tabs[0]).trigger('click');
        }
        $('.tab-container').show();
      })
      .fail(omegaup.UI.apiError);
});
