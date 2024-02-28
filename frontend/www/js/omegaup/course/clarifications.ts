import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';

import course_Clarifications from '../components/course/Clarifications.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseClarificationsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-clarifications': course_Clarifications,
    },
    render: function (createElement) {
      return createElement('omegaup-course-clarifications', {
        props: {
          isAdmin: payload.is_admin || payload.is_teaching_assistant,
          clarifications: payload.clarifications,
          pagerItems: payload.pagerItems,
          pageSize: payload.length,
          page: payload.page,
        },
        on: {
          'clarification-response': (clarification: types.Clarification) => {
            api.Clarification.update(clarification)
              .then(() => window.location.reload())
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
