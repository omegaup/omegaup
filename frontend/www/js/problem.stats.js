omegaup.OmegaUp.on('ready', function() {
  var problemAlias =
      /\/problem\/([^\/]+)\/stats\/?.*/.exec(window.location.pathname)[1];

  Highcharts.setOptions({global: {useUTC: false}});

  var stats = null;
  var callStatsApiTimeout = 10 * 1000;
  var updateRunCountsChart = callStatsApiTimeout;
  var updatePendingRunsChartTimeout = callStatsApiTimeout / 2;

  function getStats() {
    omegaup.API.Problem.stats({problem_alias: problemAlias})
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

  function getCasesDistribution() {
    var casesCounts = [];
    for (var k in stats.cases_stats) {
      casesCounts.push(parseInt(stats.cases_stats[k]));
    }

    return casesCounts;
  }

  function updateRunCountsData() {
    window.run_counts_chart.series[0].setData(oGraph.normalizeRunCounts(stats));
    window.cases_distribution_chart.series[0].setData(getCasesDistribution());
    setTimeout(updateRunCountsData, updateRunCountsChart);
  }

  function drawCharts() {
    $('#total-runs')
        .text(omegaup.UI.formatString(omegaup.T.totalRuns,
                                      {numRuns: stats.total_runs}));

    if (window.run_counts_chart != null) {
      return;
    }

    // Draw verdict counts pie chart
    window.run_counts_chart =
        oGraph.verdictCounts('verdict-chart', problemAlias, stats);

    var casesNames = [];
    var casesCounts = [];
    for (var k in stats.cases_stats) {
      casesNames.push(k);
      casesCounts.push(parseInt(stats.cases_stats[k]));
    }

    // Cases distribution chart
    window.cases_distribution_chart = new Highcharts.Chart({
      chart: {type: 'column', renderTo: 'cases-distribution-chart'},
      title: {text: 'Soluciones correctas caso por caso'},
      xAxis: {categories: casesNames},
      yAxis: {min: 0, title: {text: 'Número de soluciones'}},
      tooltip: {},
      plotOptions: {column: {pointPadding: 0.2, borderWidth: 0}},
      series: [{name: 'Número de soluciones', data: casesCounts}]
    });
  }

  getStats();

  setTimeout(updateRunCountsData, updateRunCountsChart);

  // Pending runs chart
  window.pending_chart =
      oGraph.pendingRuns(updatePendingRunsChartTimeout,
                         function() { return stats.pending_runs.length; });
});
