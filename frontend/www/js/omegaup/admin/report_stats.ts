import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';
import admin_ReportStats from '../components/admin/ReportStats.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ReportStatsPayload();

  const startTime = new Date();
  const endTime = new Date();

  startTime.setFullYear(startTime.getFullYear(), 0, 1);

  const reportStats = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-report-stats': admin_ReportStats,
    },
    data: () => ({
      report: payload.report,
    }),
    render: function (createElement) {
      return createElement('omegaup-admin-report-stats', {
        props: {
          report: this.report,
          startTime,
          endTime,
        },
        on: {
          'update-report': ({
            startTime,
            endTime,
          }: {
            startTime: Date;
            endTime: Date;
          }) => {
            const startTimestamp = startTime.getTime() / 1000;
            const endTimestamp = endTime.getTime() / 1000;

            api.Admin.platformReportStats({
              start_time: startTimestamp,
              end_time: endTimestamp,
            })
              .then((data) => {
                reportStats.report = data.report;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
