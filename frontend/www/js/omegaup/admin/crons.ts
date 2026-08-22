import admin_Crons from '../components/admin/Crons.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { types } from '../api_types';

const REFRESH_INTERVAL_MS = 15000;

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CronsDetailsPayload();

  const app = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-crons': admin_Crons,
    },
    data: {
      jobs: payload.jobs,
      runs: payload.runs,
    },
    render: function (createElement) {
      return createElement('omegaup-admin-crons', {
        props: {
          jobs: this.jobs,
          runs: this.runs,
        },
        on: {
          'set-enabled': ({
            name,
            enabled,
          }: {
            name: string;
            enabled: boolean;
          }) => {
            api.Admin.setCronJobEnabled({ name, enabled })
              .then(() => {
                ui.success(T.cronControlPlaneEnabledUpdated);
                refresh();
              })
              .catch(ui.apiError);
          },
          rerun: (name: string) => {
            api.Admin.rerunCron({ name })
              .then(() => {
                ui.success(T.cronControlPlaneRerunQueued);
                refresh();
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  function refresh(): void {
    api.Admin.getCrons()
      .then((response) => {
        app.jobs = response.jobs;
        app.runs = response.runs;
      })
      .catch(() => undefined);
  }

  window.setInterval(refresh, REFRESH_INTERVAL_MS);
});
