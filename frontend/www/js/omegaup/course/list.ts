import course_CardsList from '../components/course/CardsList.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseListPayload();
  new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-course-cards-list', {
        props: {
          courses: payload.courses,
        },
      });
    },
    components: {
      'omegaup-course-cards-list': course_CardsList,
    },
  });
});
