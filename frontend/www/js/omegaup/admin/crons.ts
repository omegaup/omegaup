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
                app.jobs = app.jobs.map((job) =>
                  job.name === name ? { ...job, enabled } : job,
                );
                ui.success(T.cronControlPlaneEnabledUpdated);
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

  let latestRefresh = 0;
  let refreshing = false;

  function refresh(): void {
    if (refreshing) {
      return;
    }
    refreshing = true;
    const sequence = ++latestRefresh;
    api.Admin.getCrons()
      .then((response) => {
        // A slower earlier request must not land on top of a newer one.
        if (sequence !== latestRefresh) {
          return;
        }
        app.jobs = response.jobs;
        app.runs = response.runs;
      })
      .catch(() => undefined)
      .finally(() => {
        refreshing = false;
      });
  }

  const timer = window.setInterval(() => {
    if (document.hidden) {
      return;
    }
    refresh();
  }, REFRESH_INTERVAL_MS);
  window.addEventListener('beforeunload', () => window.clearInterval(timer));
});
