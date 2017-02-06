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

  var courseForm = $('.new_course_form');
  omegaup.API.getCourseAdminDetails({alias: courseAlias})
      .then(function(course) {
        $('.course-header')
            .text(course.name)
            .attr('href', '/course/' + courseAlias + '/');
        $('#title', courseForm).val(course.name);
        $('#alias', courseForm).val(course.alias);
        $('#description', courseForm).val(course.description);
        $('#start_time', courseForm)
            .val(omegaup.UI.formatDate(course.start_time));
        $('#finish_time', courseForm)
            .val(omegaup.UI.formatDate(course.finish_time));

        $('#show_scoreboard', courseForm).val(course.show_scoreboard);
      });

  // Edit course
  courseForm
      .submit(function() {
        omegaup.API
            .updateCourse({
              course_alias: courseAlias,
              name: $('#title', courseForm).val(),
              description: $('#description', courseForm).val(),
              start_time: (new Date($('#start_time', courseForm).val())
                               .getTime()) /
                              1000,
              finish_time: (new Date($('#finish_time', courseForm).val())
                                .setHours(23, 59, 59, 999)) /
                               1000,
              alias: $('#alias', courseForm).val(),
              show_scoreboard: $('#show_scoreboard', courseForm).val(),
            })
            .then(function(data) {
              omegaup.UI.success('Tu curso ha sido editado! <a href="/course/' +
                                 $('#alias', courseForm).val() + '">' +
                                 omegaup.T.courseEditGoToCourse + '</a>');
              $('.course-header')
                  .text($('#title', courseForm).val())
                  .attr('href', '/course/' + courseAlias + '/');
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
