import admin_Crons from '../components/admin/Crons.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CronsDetailsPayload();

  const app = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-crons': admin_Crons,
    },
    data: () => ({
      rerunsInFlight: [] as string[],
    }),
    render: function (createElement) {
      return createElement('omegaup-admin-crons', {
        props: {
          jobs: payload.jobs,
          runs: payload.runs,
          problemHealthFindings: payload.problemHealthFindings,
          rerunsInFlight: this.rerunsInFlight,
        },
        on: {
          rerun: (name: string) => {
            // A second click would only be told a rerun is already waiting.
            app.rerunsInFlight.push(name);
            api.Admin.rerunCron({ name })
              .then((response) => {
                if (response.queued) {
                  ui.success(T.cronControlPlaneRerunQueued);
                } else {
                  ui.info(T.cronControlPlaneRerunAlreadyQueued);
                }
              })
              .catch(ui.apiError)
              .finally(() => {
                app.rerunsInFlight = app.rerunsInFlight.filter(
                  (job) => job !== name,
                );
              });
          },
        },
      });
    },
  });
});
