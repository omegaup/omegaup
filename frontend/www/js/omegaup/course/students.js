import course_ViewProgress from '../components/course/ViewProgress.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var courseAlias =
      /\/course\/([^\/]+)\/students\/?.*/.exec(window.location.pathname)[1];

  var payload = JSON.parse(document.getElementById('payload').innerText);

  var viewProgress = new Vue({
    el: '#view-progress',
    render: function(createElement) {
      return createElement('omegaup-course-viewprogress', {
        props: {
          T: T,
          course: payload.course,
          students: payload.students,
          assignments: payload.course.assignments
        },
      });
    },
    components: {
      'omegaup-course-viewprogress': course_ViewProgress,
    },
  });
});
