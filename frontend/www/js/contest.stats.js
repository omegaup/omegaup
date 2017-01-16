omegaup.OmegaUp.on('ready', function() {
  var contestAlias = /\/contest\/([^\/]+)\/stats\/?.*/.exec(
    window.location.pathname
  )[1];

  Highcharts.setOptions({ global: { useUTC: false } });

  var stats = null;
  var callStatsApiTimeout = 10 * 1000;
  var updateRunCountsChartTimeout = callStatsApiTimeout;
  var updatePendingRunsChartTimeout = callStatsApiTimeout / 2;

  function getStats() {
    omegaup.API
      .getContestStats({ contest_alias: contestAlias })
      .then(function(s) {
        stats = s;
        drawCharts();
      });
    updateStats();
  }

  function updateStats() {
    setTimeout(
      function() {
        getStats();
      },
      callStatsApiTimeout
    );
  }

  function updateRunCountsData() {
    window.run_counts_chart.series[0].setData(oGraph.normalizeRunCounts(stats));
    window.distribution_chart.series[0].setData(oGraph.getDistribution(stats));
    setTimeout(updateRunCountsData, updateRunCountsChartTimeout);
  }

  function drawCharts() {
    $('#total-runs').html('Total de envíos: ' + stats.total_runs);

    // This function is called after we call getStats multiple times. We
    // just need to draw once.
    if (window.run_counts_chart != null) {
      return;
    }

    // Draw verdict counts pie chart
    window.run_counts_chart = oGraph.verdictCounts(
      'verdict-chart',
      contestAlias,
      stats
    );

    // Draw distribution of scores chart
    window.distribution_chart = oGraph.distributionChart(
      'distribution-chart',
      contestAlias,
      stats
    );
  }

  getStats();

  setTimeout(updateRunCountsData, updateRunCountsChartTimeout);

  // Pending runs chart
  window.pending_chart = oGraph.pendingRuns(
    updatePendingRunsChartTimeout,
    function() {
      return stats.pending_runs.length;
    }
  );
});
