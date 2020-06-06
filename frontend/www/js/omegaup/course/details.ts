import Vue from 'vue';
import course_Details from '../components/course/Details.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseDetailsPayload();
  const courseDetails = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-course-details', {
        props: {
          course: payload.details,
          progress: payload.progress,
        },
      });
    },
    components: {
      'omegaup-course-details': course_Details,
    },
  });
});
