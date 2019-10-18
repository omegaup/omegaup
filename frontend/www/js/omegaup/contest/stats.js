import contest_Stats from '../components/contest/Stats.vue';
import Vue from 'vue';
import { API, OmegaUp } from '../omegaup.js';

OmegaUp.on('ready', function() {
  const contestAlias = /\/contest\/([^\/]+)\/stats\/?.*/.exec(
    window.location.pathname,
  )[1];

  Highcharts.setOptions({ global: { useUTC: false } });
  const callStatsApiTimeout = 10 * 1000;
  const updatePendingRunsChartTimeout = callStatsApiTimeout / 2;

  let stats = new Vue({
    el: '#contest-stats',
    render: function(createElement) {
      return createElement('omegaup-contest-stats', {
        props: {
          stats: this.stats,
          contestAlias: this.contestAlias,
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
      contestAlias: contestAlias,
    },
    components: {
      'omegaup-contest-stats': contest_Stats,
    },
  });
  let pendingChart = oGraph.pendingRuns(
    updatePendingRunsChartTimeout,
    () => stats.stats.pending_runs.length,
  );

  function getStats() {
    API.Contest.stats({ contest_alias: contestAlias })
      .then(s => Vue.set(stats, 'stats', s))
      .fail(omegaup.UI.apiError);
  }

  setInterval(() => getStats(), callStatsApiTimeout);
  getStats();
});
