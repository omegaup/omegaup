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
      problemHealthFindings: payload.problemHealthFindings,
    },
    render: function (createElement) {
      return createElement('omegaup-admin-crons', {
        props: {
          jobs: this.jobs,
          runs: this.runs,
          problemHealthFindings: this.problemHealthFindings,
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
                // A refresh already on its way would put the old state back.
                latestRefresh++;
                app.jobs = app.jobs.map((job) =>
                  job.name === name ? { ...job, enabled } : job,
                );
                ui.success(T.cronControlPlaneEnabledUpdated);
              })
              .catch(ui.apiError);
          },
          rerun: (name: string) => {
            api.Admin.rerunCron({ name })
              .then((response) => {
                if (response.queued) {
                  ui.success(T.cronControlPlaneRerunQueued);
                } else {
                  ui.info(T.cronControlPlaneRerunAlreadyQueued);
                }
                refresh();
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  let latestRefresh = 0;

  function refresh(): void {
    const sequence = ++latestRefresh;
    api.Admin.getCrons()
      .then((response) => {
        // An answer older than the newest request or change is out of date.
        if (sequence !== latestRefresh) {
          return;
        }
        app.jobs = response.jobs;
        app.runs = response.runs;
        app.problemHealthFindings = response.problemHealthFindings;
      })
      // A failed background refresh keeps what is on screen.
      .catch(() => undefined);
  }

  window.setInterval(() => {
    if (!document.hidden) {
      refresh();
    }
  }, REFRESH_INTERVAL_MS);
});
