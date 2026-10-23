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

  let latestRefresh = 0;
  let refreshing = false;
  let refreshFailed = false;

  const app = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-crons': admin_Crons,
    },
    data: {
      jobs: payload.jobs,
      runs: payload.runs,
      problemHealthFindings: payload.problemHealthFindings,
      rerunsInFlight: [] as string[],
    },
    render: function (createElement) {
      return createElement('omegaup-admin-crons', {
        props: {
          jobs: this.jobs,
          runs: this.runs,
          problemHealthFindings: this.problemHealthFindings,
          rerunsInFlight: this.rerunsInFlight,
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
                ui.success(
                  ui.formatString(
                    enabled
                      ? T.cronControlPlaneJobNowEnabled
                      : T.cronControlPlaneJobNowDisabled,
                    { name },
                  ),
                );
              })
              .catch(ui.apiError);
          },
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

  function refresh(): void {
    if (refreshing || document.hidden) {
      return;
    }
    refreshing = true;
    const sequence = ++latestRefresh;
    api.Admin.getCrons()
      .then((response) => {
        refreshFailed = false;
        // An answer older than the newest request or change is out of date.
        if (sequence !== latestRefresh) {
          return;
        }
        app.jobs = response.jobs;
        app.runs = response.runs;
        app.problemHealthFindings = response.problemHealthFindings;
      })
      .catch((error) => {
        // Said once, so an expired session does not repeat it every tick.
        if (!refreshFailed) {
          refreshFailed = true;
          ui.apiError(error);
        }
      })
      .finally(() => {
        refreshing = false;
      });
  }

  window.setInterval(refresh, REFRESH_INTERVAL_MS);
  document.addEventListener('visibilitychange', refresh);
});
