import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import problem_SettingsSummary from '../components/problem/SettingsSummary.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemSettingsSummaryPayload(
    'settings-summary-payload',
  );
  new Vue({
    el: '#problem-settings-summary',
    render: function (createElement) {
      return createElement('omegaup-problem-settings-summary', {
        props: {
          problem: payload.problem,
          showVisibilityIndicators: true,
          showEditLink: payload.problem_admin,
        },
      });
    },
    components: {
      'omegaup-problem-settings-summary': problem_SettingsSummary,
    },
  });
});
