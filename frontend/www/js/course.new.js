omegaup.OmegaUp.on('ready', function() {
  $('.new_course_form')
      .submit(function() {
        omegaup.API
            .createCourse({
              alias: $('.new_course_form #alias').val(),
              name: $('.new_course_form #title').val(),
              description: $('.new_course_form #description').val(),
              start_time: (new Date($('.new_course_form #start_time').val())
                               .getTime()) /
                              1000,
              finish_time: (new Date($('.new_course_form #finish_time').val())
                                .setHours(23, 59, 0, 0)) /
                               1000,
              public: 0,
              show_scoreboard: $('.new_course_form #show_scoreboard').val(),
            })
            .then(function(data) {
              if (data.status == 'ok')
                window.location.replace('/course/' +
                                        $('.new_course_form #alias').val() +
                                        '/edit/#problems');
            });

        return false;
      });
});
