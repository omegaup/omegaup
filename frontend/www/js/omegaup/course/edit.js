import API from '../api.js';
import UI from '../ui.js';
import Vue from 'vue';
import course_ViewProgress from '../components/course/ViewProgress.vue';
import {OmegaUp, T} from '../omegaup.js';

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

  var viewProgress = new Vue({
    el: '#view-progress div',
    render: function(createElement) {
      return createElement('omegaup-course-viewprogress', {
        props: {
          T: T,
          students: this.students,
          totalHomeworks: this.totalHomeworks,
          totalTests: this.totalTests,
        },
      });
    },
    data: {
      students: [],
      totalHomeworks: 0,
      totalTests: 0,
    },
    components: {
      'omegaup-course-viewprogress': course_ViewProgress,
    },
  });

  var courseForm = $('.new_course_form');
  API.Course.adminDetails({alias: courseAlias})
      .then(function(course) {
        $('.course-header')
            .text(course.name)
            .attr('href', '/course/' + courseAlias + '/');
        $('#title', courseForm).val(course.name);
        $('#alias', courseForm).val(course.alias);
        $('#description', courseForm).val(course.description);
        $('#show_scoreboard', courseForm).val(course.show_scoreboard);
        $('#start_time', courseForm).val(UI.formatDate(course.start_time));
        $('#finish_time', courseForm).val(UI.formatDate(course.finish_time));

        $('#start_time, #finish_time', courseForm)
            .datepicker({
              weekStart: 1,
              format: 'mm/dd/yyyy',
            });
      });

  // Edit course
  courseForm.submit(function(ev) {
    ev.preventDefault();
    API.Course.update({
                course_alias: courseAlias,
                name: $('#title', courseForm).val(),
                description: $('#description', courseForm).val(),
                start_time:
                    (new Date($('#start_time', courseForm).val()).getTime()) /
                        1000,
                finish_time: (new Date($('#finish_time', courseForm).val())
                                  .setHours(23, 59, 59, 999)) /
                                 1000,
                alias: $('#alias', courseForm).val(),
                show_scoreboard: $('#show_scoreboard', courseForm).val(),
              })
        .then(function(data) {
          UI.success('Tu curso ha sido editado! <a href="/course/' +
                     $('#alias', courseForm).val() + '">' +
                     T.courseEditGoToCourse + '</a>');
          $('.course-header')
              .text($('#title', courseForm).val())
              .attr('href', '/course/' + courseAlias + '/');
          $('div.post.footer').show();
          window.scrollTo(0, 0);
        });
  });

  $('#add-member-form')
      .submit(function(ev) {
        ev.preventDefault();

        API.Course.addStudent({
                    course_alias: courseAlias,
                    usernameOrEmail: $('#member-username').val()
                  })
            .then(function(data) {
              refreshStudentList();
              UI.success(T.courseStudentAdded);
            })
            .fail(UI.apiError);
      });

  function refreshStudentList() {
    API.Course.listStudents({course_alias: courseAlias})
        .then(function(data) {
          viewProgress.totalHomeworks = data['counts']['homework'] || 0;
          viewProgress.totalTests = data['counts']['homework'] || 0;
          viewProgress.students = data['students'];
        })
        .fail(UI.apiError);
  }

  refreshStudentList();
});
