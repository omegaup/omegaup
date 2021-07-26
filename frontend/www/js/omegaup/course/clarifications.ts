import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';

import course_Clarications from '../components/course/Clarifications.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseClarificationsPayload();
  const headerPayload = types.payloadParsers.CommonPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-clarifications': course_Clarications,
    },
    render: function (createElement) {
      return createElement('omegaup-course-clarifications', {
        props: {
          isAdmin: headerPayload.isAdmin,
          clarifications: payload.clarifications,
          pagerItems: payload.pagerItems,
          length: payload.length,
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
