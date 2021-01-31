import course_List from '../components/course/List.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseListPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-cards-list': course_List,
    },
    render: function (createElement) {
      return createElement('omegaup-course-cards-list', {
        props: {
          courses: payload.courses,
          loggedIn: headerPayload.isLoggedIn,
        },
      });
    },
  });
});
