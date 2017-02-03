omegaup.OmegaUp.on('ready', function() {
      var courseAlias =
          /\/course\/([^\/]+)\/?/.exec(window.location.pathname)[1];

      function onCourseLoaded(course) {
        if (course.status == 'ok') {
          $('.course #title').html(omegaup.UI.escape(course.name));
          $('.course #description').html(omegaup.UI.escape(course.description));
          $('.course #start_time')
              .val(omegaup.UI.formatDateTime(course.start_time));
          $('.course #finish_time')
              .val(omegaup.UI.formatDateTime(course.finish_time));
        }
      }

      omegaup.API.getCourseDetails({alias: courseAlias}).then(onCourseLoaded);
    });
