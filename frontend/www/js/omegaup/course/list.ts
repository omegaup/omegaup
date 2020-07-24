import course_List from '../components/course/List.vue';
import course_CardsList from '../components/course/CardsList.vue';
import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseListPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  // TODO: Uncomment the line below when #4422 be accepted and merged
  // if (!payload.access_mode) {
  if (true) {
    const courseCardsList = new Vue({
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
  } else {
    const courseList = new Vue({
      el: '#main-container',
      render: function (createElement) {
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
  }
});
