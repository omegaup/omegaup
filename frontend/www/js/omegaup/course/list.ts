// TODO: Replace List.vue with CardsList.vue when PR #4422 is merged
import course_CardsList from '../components/course/CardsList.vue';
import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseListPayload();
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
});
