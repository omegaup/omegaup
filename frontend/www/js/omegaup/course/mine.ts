import course_List from '../components/course/Mine.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseListMinePayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-list': course_List,
    },
    render: function (createElement) {
      return createElement('omegaup-course-list', {
        props: {
          courses: payload.courses,
          isMainUserIdentity: headerPayload?.isMainUserIdentity ?? false,
        },
      });
    },
  });
});
