import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import course_Tabs from '../components/course/Tabs.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseTabsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-tabs': course_Tabs,
    },
    render: function (createElement) {
      return createElement('omegaup-course-tabs', {
        props: {
          courses: payload.courses,
          loggedIn: commonPayload.isLoggedIn,
        },
      });
    },
  });
});
