omegaup.OmegaUp.on('ready', function() {
  var courseAlias = /\/course\/([^\/]+)\/?/.exec(window.location.pathname)[1];

  function onCourseLoaded(course) {
    if (course.status == 'ok') {
      $('.course #title').text(course.name);
      $('.course #description').text(course.description);
      $('.course #start_time')
          .val(omegaup.UI.formatDateTime(course.start_time));
      $('.course #finish_time')
          .val(omegaup.UI.formatDateTime(course.finish_time));
    }
  }

  omegaup.API.Course.details({alias: courseAlias})
      .then(onCourseLoaded)
      .fail(omegaup.UI.apiError);
});
