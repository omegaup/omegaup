import course_SubmissionsList from '../components/activity/SubmissionsList.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseSubmissionsListPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-submissions-list': course_SubmissionsList,
    },
    render: function (createElement) {
      return createElement('omegaup-course-submissions-list', {
        props: {
          solvedProblems: payload.solvedProblems,
          unsolvedProblems: payload.unsolvedProblems,
        },
      });
    },
  });
});
