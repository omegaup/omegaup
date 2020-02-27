function OmegaupGraph() {}

OmegaupGraph.prototype.verdictCounts = function(renderTo, title, stats) {
  return new Highcharts.Chart({
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      renderTo: renderTo,
    },
    title: {
      text: omegaup.UI.formatString(omegaup.T.wordsVerdictsOf, {
        entity: title,
      }),
    },
    tooltip: {
      formatter: function() {
        return omegaup.UI.formatString(omegaup.T.wordsNumberOfRuns, {
          number: this.point.y,
        });
      },
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: true,
          color: '#000000',
          connectorColor: '#000000',
          formatter: function() {
            return (
              '<b>' +
              this.point.name +
              '</b>: ' +
              this.percentage.toFixed(2) +
              '% (' +
              this.point.y +
              ')'
            );
          },
        },
      },
    },
    series: [
      {
        type: 'pie',
        name: omegaup.T.wordsProportion,
        data: this.normalizeRunCounts(stats),
      },
    ],
  });
};

OmegaupGraph.prototype.normalizeRunCounts = function(stats) {
  var result = [];
  for (var verdict in stats.verdict_counts) {
    if (!stats.verdict_counts.hasOwnProperty(verdict)) continue;
    if (verdict == 'NO-AC') continue;
    if (verdict == 'AC') {
      result.push({
        name: verdict,
        y: stats.verdict_counts[verdict],
        sliced: true,
        selected: true,
      });
      continue;
    }
    result.push([verdict, stats.verdict_counts[verdict]]);
  }
  return result;
};

OmegaupGraph.prototype.pendingRuns = function(refreshRate, updateStatsFn) {
  return new Highcharts.Chart({
    chart: {
      type: 'spline',
      animation: Highcharts.svg, // don't animate in old IE
      marginRight: 10,
      renderTo: document.querySelector('.pending-runs-chart'),
      events: {
        load: function() {
          // set up the updating of the chart each second
          var series = this.series[0];
          setInterval(function() {
            var x = new Date().getTime(), // current time
              y = updateStatsFn();
            series.addPoint([x, y], true, true);
          }, refreshRate);
        },
      },
    },
    title: { text: omegaup.T.wordsSubmissionsNotYetReviewed },
    xAxis: { type: 'datetime', tickPixelInterval: 200 },
    yAxis: {
      title: { text: 'Total' },
      plotLines: [{ value: 0, width: 1, color: '#808080' }],
    },
    tooltip: {
      formatter: function() {
        return (
          '<b>' +
          this.series.name +
          '</b><br/>' +
          Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) +
          '<br/>' +
          Highcharts.numberFormat(this.y, 2)
        );
      },
    },
    legend: { enabled: false },
    exporting: { enabled: false },
    series: [
      {
        name: omegaup.T.wordsPendingRuns,
        data: (function() {
          // generate an array of random data
          var data = [],
            time = new Date().getTime(),
            i;

          for (i = -5; i <= 0; i++) {
            data.push({ x: time + i * 1000, y: 0 });
          }
          return data;
        })(),
      },
    ],
  });
};

OmegaupGraph.prototype.getDistribution = function(stats) {
  var distribution = [];

  for (var val in stats.distribution) {
    distribution.push(parseInt(stats.distribution[val]));
  }

  return distribution;
};

OmegaupGraph.prototype.distributionChart = function(renderTo, title, stats) {
  var categories_vals = [];
  var separator = 0;
  for (var val in stats.distribution) {
    categories_vals[val] = separator;
    separator += stats.size_of_bucket;
  }

  return new Highcharts.Chart({
    chart: { type: 'column', renderTo: renderTo },
    title: {
      text: omegaup.UI.formatString(omegaup.T.wordsPointsDistribution, {
        number: title,
      }),
    },
    xAxis: {
      categories: categories_vals,
      title: { text: omegaup.T.wordsPointsDistributionInIntervals },
      labels: {
        formatter: function() {
          if (this.value % 10 == 0) {
            return this.value;
          } else {
            return '';
          }
        },
      },
    },
    yAxis: { min: 0, title: { text: omegaup.T.wordsNumberOfContestants } },
    tooltip: {},
    plotOptions: { column: { pointPadding: 0.2, borderWidth: 0 } },
    series: [
      {
        name: omegaup.T.wordsNumberOfContestants,
        data: this.getDistribution(stats),
      },
    ],
  });
};

var oGraph = new OmegaupGraph();
