import course_List from '../components/course/List.vue';
import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseListPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  let courseList = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-course-list', {
        props: {
          courses: payload.courses,
          isMainUserIdentity: headerPayload?.isMainUserIdentity,
        },
      });
    },
    components: {
      'omegaup-course-list': course_List,
    },
  });
});
