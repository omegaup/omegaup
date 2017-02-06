omegaup.OmegaUp.on('ready', function() {
  if (window.location.hash) {
    $('#sections').find('a[href="' + window.location.hash + '"]').tab('show');
  }

  $('#sections')
      .on('click', 'a', function(e) {
        e.preventDefault();
        // add this line
        window.location.hash = $(this).attr('href');
        $(this).tab('show');
      });

  var courseAlias =
      /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

  omegaup.API.getCourseAdminDetails({alias: courseAlias})
      .then(function(course) {
        $('.page-header h1 span')
            .html('<a href="/course/' + courseAlias + '/">' + course.name +
                  '</a>');
        $('.new_course_form #title').val(course.name);
        $('.new_course_form #alias').val(course.alias);
        $('.new_course_form #description').val(course.description);
        $('.new_course_form #start_time')
            .val(omegaup.UI.formatDate(course.start_time));
        $('.new_course_form #finish_time')
            .val(omegaup.UI.formatDate(course.finish_time));

        if (course.window_length === null) {
          // Disable window length
          $('#window_length_enabled').removeAttr('checked');
          $('#window_length').val('');
        } else {
          $('#window_length_enabled').attr('checked', 'checked');
          $('#window_length').removeAttr('disabled');
          $('#window_length').val(course.window_length);
        }

        $('.new_course_form #show_scoreboard').val(course.show_scoreboard);

        $('.contest-publish-form #public').val(course.public);

        if (course.contestant_must_register == null ||
            course.contestant_must_register == '0') {
          $('#requests').hide();
        }
      });

  // Edit course
  $('.new_course_form')
      .submit(function() {
        omegaup.API
            .updateCourse({
              course_alias: courseAlias,
              name: $('.new_course_form #title').val(),
              description: $('.new_course_form #description').val(),
              start_time: (new Date($('.new_course_form #start_time').val())
                               .getTime()) /
                              1000,
              finish_time: (new Date($('.new_course_form #finish_time').val())
                                .setHours(23, 59, 59, 999)) /
                               1000,
              alias: $('.new_course_form #alias').val(),
              show_scoreboard: $('.new_course_form #show_scoreboard').val(),
            })
            .then(function(data) {
              omegaup.UI.success('Tu curso ha sido editado! <a href="/course/' +
                                 $('.new_course_form #alias').val() + '">' +
                                 omegaup.T.courseEditGoToCourse + '</a>');
              $('div.post.footer').show();
              window.scrollTo(0, 0);
            });
        return false;
      });
});

var koStudentsList = {
  students: ko.observableArray(),
};

function refreshStudentList() {
  var courseAlias =
      /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

  omegaup.API.getCourseStudentList({course_alias: courseAlias})
      .then(function(data) {
        if (data.status != 'ok') {
          // TODO: Delete this when resolve vs. reject is fixed.
          return;
        }

        koStudentsList.students.removeAll();
        for (var i = 0; i < data['students'].length; ++i) {
          var student = data['students'][i];
          student.remove = function(student) {
            omegaup.API.removeStudentFromCourse({
              course_alias: courseAlias,
              usernameOrEmail: student.username
            })
            .then(function(data) {
              refreshStudentList();
              omegaup.UI.success(omegaup.T.courseStudentRemoved);
            })
            .fail(function(data) { omegaup.UI.error(data.error); });
          };
          student.profileURL = '/profile/' + student.username;

          var totalHomeworks = (data['counts']['homework'] != null) ?
                                   data['counts']['homework'] :
                                   0;
          var totalTests =
              (data['counts']['test'] != null) ? data['counts']['test'] : 0;
          student.totalHomeworks =
              student.count_homeworks_done + '/' + totalHomeworks;
          student.totalTests = student.count_tests_done + '/' + totalTests;

          koStudentsList.students.push(student);
        }
      });
}
