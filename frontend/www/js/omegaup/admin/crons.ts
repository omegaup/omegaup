import admin_Crons from '../components/admin/Crons.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CronsDetailsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-crons': admin_Crons,
    },
    render: function (createElement) {
      return createElement('omegaup-admin-crons', {
        props: {
          jobs: payload.jobs,
          runs: payload.runs,
        },
        on: {
          rerun: (name: string) => {
            api.Admin.rerunCron({ name })
              .then(() => {
                ui.success(T.cronControlPlaneRerunQueued);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
