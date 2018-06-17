import contest_Stats from '../components/contest/Stats.vue';
import Vue from 'vue';
import {OmegaUp} from '../omegaup.js';

OmegaUp.on('ready', function() {
  var verdict = null;
  let Stats = new Vue({
    el: '#contest-stats',
    render: function(createElement) { return createElement('contestStats'); },
    mounted: function() {
      verdict = this.$el.querySelector('.copy').querySelectorAll('div')[0];
    },
    components: {
      'contestStats': contest_Stats,
    },
  });
  var contestAlias =
      /\/contest\/([^\/]+)\/stats\/?.*/.exec(window.location.pathname)[1];

  Highcharts.setOptions({global: {useUTC: false}});

  var stats = null;
  var callStatsApiTimeout = 10 * 1000;
  var updateRunCountsChartTimeout = callStatsApiTimeout;
  var updatePendingRunsChartTimeout = callStatsApiTimeout / 2;
  function getStats() {
    omegaup.API.Contest.stats({contest_alias: contestAlias})
        .then(function(s) {
          stats = s;
          drawCharts();
        })
        .fail(omegaup.UI.apiError);
    updateStats();
  }

  function updateStats() {
    setTimeout(function() { getStats(); }, callStatsApiTimeout);
  }

  function updateRunCountsData() {
    window.run_counts_chart.series[0].setData(oGraph.normalizeRunCounts(stats));
    window.distribution_chart.series[0].setData(oGraph.getDistribution(stats));
    setTimeout(updateRunCountsData, updateRunCountsChartTimeout);
  }

  function drawCharts() {
    $($('.total-runs')[0])
        .text(omegaup.UI.formatString(omegaup.T.totalRuns,
                                      {numRuns: stats.total_runs}));

    // This function is called after we call getStats multiple times. We
    // just need to draw once.
    if (window.run_counts_chart != null) {
      return;
    }

    // Draw verdict counts pie chart
    window.run_counts_chart =
        oGraph.verdictCounts(verdict, contestAlias, stats);

    // Draw distribution of scores chart
    window.distribution_chart = oGraph.distributionChart(
        $('.distribution-chart')[0], contestAlias, stats);
  }

  getStats();

  setTimeout(updateRunCountsData, updateRunCountsChartTimeout);

  // Pending runs chart
  window.pending_chart =
      oGraph.pendingRuns(updatePendingRunsChartTimeout,
                         function() { return stats.pending_runs.length; });
});
