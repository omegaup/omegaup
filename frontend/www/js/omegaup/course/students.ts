import course_ViewProgress from '../components/course/ViewProgress.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.StudentsProgressPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-viewprogress': course_ViewProgress,
    },
    render: function (createElement) {
      return createElement('omegaup-course-viewprogress', {
        props: {
          T: T,
          course: payload.course,
          students: payload.students,
          problemTitles: payload.problemTitles,
          assignments: payload.course.assignments,
        },
      });
    },
  });
});
