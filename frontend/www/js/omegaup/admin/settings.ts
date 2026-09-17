import admin_Settings from '../components/admin/Settings.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';
import T from '../lang';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-settings': admin_Settings,
    },
    render: function (createElement) {
      return createElement('omegaup-admin-settings', {
        props: {
          ephemeralGraderEnabled: payload.ephemeralGraderEnabled,
        },
        on: {
          'update-ephemeral-grader': (value: boolean) => {
            api.Admin.updateSystemSettings({ ephemeral_grader_enabled: value })
              .then(() => {
                ui.success(T.ephemeralGraderUpdated);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
