import Vue from 'vue';
import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import problem_SettingsSummary from '../components/problem/SettingsSummary.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemDetailsPayload();
  const problem: omegaup.ArenaProblem = {
    problem_id: payload.problem_id,
    title: payload.title,
    alias: payload.alias,
    commit: payload.commit,
    languages: payload.languages,
    points: payload.points,
    input_limit: payload.input_limit,
    settings: payload.settings,
    quality_seal: payload.quality_seal,
    visibility: payload.visibility,
  };
  const problemSettingsSummary = new Vue({
    el: '#problem-settings-summary',
    render: function(createElement) {
      return createElement('omegaup-problem-settings-summary', {
        props: {
          problem: problem,
          inArena: false,
          isAdmin: payload.problem_admin,
        },
      });
    },
    components: {
      'omegaup-problem-settings-summary': problem_SettingsSummary,
    },
  });
});
