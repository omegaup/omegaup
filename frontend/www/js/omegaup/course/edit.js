import course_AddStudents from '../components/course/AddStudents.vue';
import course_Details from '../components/course/Details.vue';
import course_ViewProgress from '../components/course/ViewProgress.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
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

  var details = new Vue({
    el: '#edit div',
    render: function(createElement) {
      return createElement('omegaup-course-details', {
        props: {T: T, update: true, course: this.course},
        on: {
          submit: function(ev) {
            API.Course.update({
                        course_alias: courseAlias,
                        name: ev.name,
                        description: ev.description,
                        start_time: ev.startTime.getTime() / 1000,
                        finish_time:
                            new Date(ev.finishTime).setHours(23, 59, 59, 999) /
                                1000,
                        alias: ev.alias,
                        show_scoreboard: ev.showScoreboard,
                      })
                .then(function(data) {
                  UI.success('Tu curso ha sido editado! <a href="/course/' +
                             ev.alias + '">' + T.courseEditGoToCourse + '</a>');
                  $('.course-header')
                      .text(ev.alias)
                      .attr('href', '/course/' + ev.alias + '/');
                  $('div.post.footer').show();
                  window.scrollTo(0, 0);
                })
                .fail(UI.apiError);
          },
        },
      });
    },
    data: {
      course: {},
    },
    components: {
      'omegaup-course-details': course_Details,
    },
  });

  var viewProgress = new Vue({
    el: '#view-progress div',
    render: function(createElement) {
      return createElement('omegaup-course-viewprogress', {
        props: {T: T, students: this.students, assignments: this.assignments},
      });
    },
    data: {
      students: [],
      assignments: [],
    },
    components: {
      'omegaup-course-viewprogress': course_ViewProgress,
    },
  });

  var addStudents = new Vue({
    el: '#add-students div',
    render: function(createElement) {
      return createElement('omegaup-course-addstudents', {
        props: {
          T: T,
          students: this.students,
        },
        on: {
          'add-student': function(username) {
            API.Course.addStudent({
                        course_alias: courseAlias,
                        usernameOrEmail: username,
                      })
                .then(function(data) {
                  refreshStudentList();
                  UI.success(T.courseStudentAdded);
                })
                .fail(UI.apiError);
          },
          remove: function(student) {
            API.Course.removeStudent({
                        course_alias: courseAlias,
                        usernameOrEmail: student.username
                      })
                .then(function(data) {
                  refreshStudentList();
                  UI.success(T.courseStudentRemoved);
                })
                .fail(UI.apiError);
          },
        },
      });
    },
    data: {
      students: [],
    },
    components: {
      'omegaup-course-addstudents': course_AddStudents,
    },
  });

  API.Course.adminDetails({alias: courseAlias})
      .then(function(course) {
        $('.course-header')
            .text(course.name)
            .attr('href', '/course/' + courseAlias + '/');
        details.course = course;
      })
      .fail(UI.apiError);

  function refreshStudentList() {
    API.Course.listStudents({course_alias: courseAlias})
        .then(function(data) {
          viewProgress.students = data.students;
          addStudents.students = data.students;
        })
        .fail(UI.apiError);
  }

  function refreshAssignmentsList() {
    API.Course.listAssignments({course_alias: courseAlias})
        .then(function(data) { viewProgress.assignments = data.assignments; })
        .fail(UI.apiError);
  }

  refreshStudentList();
  refreshAssignmentsList();
});
