import course_ViewProgress from '../components/course/ViewProgress.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
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
          course: payload.course,
          students: payload.students,
          assignmentsProblems: payload.assignmentsProblems,
          pagerItems: payload.pagerItems,
          totalRows: payload.totalRows,
          page: payload.page,
          length: payload.length,
        },
      });
    },
  });
});
