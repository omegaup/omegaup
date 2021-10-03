import course_List from '../components/course/Tabs.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseTabsPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-list': course_List,
    },
    render: function (createElement) {
      return createElement('omegaup-course-list', {
        props: {
          enrolledCourses: payload.courses.enrolled,
          publicCourses: payload.courses.public,
          finishedCourses: payload.courses.finished,
          loggedIn: headerPayload.isLoggedIn,
        },
      });
    },
  });
});
