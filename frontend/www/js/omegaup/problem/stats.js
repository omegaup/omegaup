import problem_Stats from '../components/problem/Stats.vue';
import Vue from 'vue';
import { API, OmegaUp } from '../omegaup.js';

OmegaUp.on('ready', function() {
  const problemAlias = /\/problem\/([^\/]+)\/stats\/?.*/.exec(
    window.location.pathname,
  )[1];

  Highcharts.setOptions({ global: { useUTC: false } });
  const callStatsApiTimeout = 10 * 1000;
  const updatePendingRunsChartTimeout = callStatsApiTimeout / 2;

  let stats = new Vue({
    el: '#problem-stats',
    render: function(createElement) {
      return createElement('omegaup-problem-stats', {
        props: {
          stats: this.stats,
          problemAlias: this.problemAlias,
        },
      });
    },
    data: {
      stats: {
        total_runs: 0,
        pending_runs: [],
        max_wait_time: 0,
        max_wait_time_guid: 0,
        verdict_counts: {},
        distribution: [],
        size_of_bucket: [],
        total_points: 0,
      },
      problemAlias: problemAlias,
    },
    components: {
      'omegaup-problem-stats': problem_Stats,
    },
  });
  let pendingChart = oGraph.pendingRuns(
    updatePendingRunsChartTimeout,
    () => stats.stats.pending_runs.length,
  );

  function getStats() {
    API.Problem.stats({ problem_alias: problemAlias })
      .then(s => Vue.set(stats, 'stats', s))
      .fail(omegaup.UI.apiError);
  }

  setInterval(() => getStats(), callStatsApiTimeout);
  getStats();
});
