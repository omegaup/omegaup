import course_ViewProgress from '../components/course/ViewProgress.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.StudentsProgressPayload();

  const viewProgress = new Vue({
    el: '#main-container',
    render: function (createElement) {
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
