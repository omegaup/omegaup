import course_ViewProgress from '../components/course/ViewProgress.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);

  var viewProgress = new Vue({
    el: '#view-progress',
    render: function(createElement) {
      return createElement('omegaup-course-viewprogress', {
        props: {
          T: T,
          course: payload.course,
          students: payload.students,
          assignments: payload.course.assignments,
        },
      });
    },
    components: {
      'omegaup-course-viewprogress': course_ViewProgress,
    },
  });
});
