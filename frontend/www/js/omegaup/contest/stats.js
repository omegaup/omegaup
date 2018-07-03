import contest_Stats from '../components/contest/Stats.vue';
import Vue from 'vue';
import {OmegaUp} from '../omegaup.js';
export const bus = new Vue();

OmegaUp.on('ready', function() {
  var stats = null;
  var contestAlias =
      /\/contest\/([^\/]+)\/stats\/?.*/.exec(window.location.pathname)[1];
  var StatsVue = new Vue({
    el: '#contest-stats',
    props: {
      abc: String,
    },
    render: function(createElement) {
      return createElement('contestStats', {
        props: {
          stats: this.stats,
          contestAlias: this.contestAlias,
        }
      });
    },
    data: {
      stats: stats,
      contestAlias: contestAlias,
    },
    components: {
      'contestStats': contest_Stats,
    },
  });

  Highcharts.setOptions({global: {useUTC: false}});
  var callStatsApiTimeout = 10 * 1000;
  var updateRunCountsChartTimeout = callStatsApiTimeout;
  var updatePendingRunsChartTimeout = callStatsApiTimeout / 2;
  function getStats() {
    omegaup.API.Contest.stats({contest_alias: contestAlias})
        .then(function(s) {
          stats = s;
          StatsVue.stats = stats;
          drawCharts();
        })
        .fail(omegaup.UI.apiError);
    updateStats();
  }

  function updateStats() {
    setTimeout(function() { getStats(); }, callStatsApiTimeout);
  }

  function drawCharts() {
    $($('.total-runs')[0])
        .text(omegaup.UI.formatString(omegaup.T.totalRuns,
                                      {numRuns: stats.total_runs}));
  }

  getStats();

  // Pending runs chart
  window.pending_chart =
      oGraph.pendingRuns(updatePendingRunsChartTimeout,
                         function() { return stats.pending_runs.length; });
});
