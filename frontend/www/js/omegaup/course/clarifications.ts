import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';

import clarification_List from '../components/arena/ClarificationList.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseClarificationsPayload();
  const headerPayload = types.payloadParsers.CommonPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-clarification-list': clarification_List,
    },
    render: function (createElement) {
      return createElement('omegaup-clarification-list', {
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
