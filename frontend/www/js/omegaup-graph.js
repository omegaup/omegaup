
function OmegaupGraph() {
  var self = this;
}

OmegaupGraph.prototype.verdictCounts = function(renderTo, title, stats) {
  runs = this.normalizeRunCounts(stats);
  return new Highcharts.Chart({
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      renderTo: renderTo
    },
    title: {
      text: omegaup.UI.formatString(omegaup.T.profileStatisticsVerdictsOf,
                                    {user: title})
    },
    tooltip: {
      formatter: function() {
        return omegaup.UI.formatString(
            omegaup.T.profileStatisticsRuns,
            {runs: runs.runs[this.point.name].count});
      }
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
            return '<b>' + this.point.name + '</b>: ' +
                   this.percentage.toFixed(2) + ' % (' +
                   runs.runs[this.point.name].count + ')';
          }
        }
      }
    },
    series: [{type: 'pie', name: 'Proporción', data: runs.percentage}]
  });
};

OmegaupGraph.prototype.verdictPeriodCounts = function(renderTo, title, stats,
                                                      type, period) {
  runs = this.normalizePeriodRunCounts(stats, period);
  var data = runs[type];
  return new Highcharts.Chart({
    chart: {type: 'column', renderTo: renderTo},
    title: {
      text: omegaup.UI.formatString(omegaup.T.profileStatisticsVerdictsOf,
                                    {user: title})
    },
    xAxis: {
      categories: runs.categories,
      title: {text: omegaup.T.profileStatisticsPeriod},
      labels: {
        rotation: -45,
      }
    },
    yAxis: {
      min: 0,
      title: {text: omegaup.T.profileStatisticsNumberOfSolvedProblems},
      stackLabels: {
        enabled: false,
        style: {
          fontWeight: 'bold',
          color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
        }
      }
    },
    legend: {
      align: 'right',
      x: -30,
      verticalAlign: 'top',
      y: 25,
      floating: true,
      backgroundColor:
          (Highcharts.theme && Highcharts.theme.background2) || 'white',
      borderColor: '#CCC',
      borderWidth: 1,
      shadow: false
    },
    tooltip: {
      headerFormat: '<b>{point.x}</b><br/>',
      pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
    },
    plotOptions: {
      column: {
        stacking: 'normal',
        dataLabels: {
          enabled: false,
          color:
              (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
        }
      }
    },
    series: data
  });
};

OmegaupGraph.prototype.normalizeRunCounts = function(stats) {
  var total = this.countRuns(stats.runs);
  var runs = this.groupRuns(stats.runs, 'verdict');
  var verdicts = ['WA', 'PA', 'AC', 'TLE', 'MLE', 'OLE', 'RTE', 'CE', 'JE'];
  var response = {runs: {}, percentage: []};
  for (var [index, verdict] of verdicts.entries()) {
    num_runs = typeof runs[verdict] == 'undefined' ? 0 : runs[verdict][verdict];
    response['runs'][verdict] = {name: verdict, count: num_runs};
    if (verdict == 'AC') {
      response['percentage'][index] = {
        name: 'AC',
        y: (num_runs / total) * 100,
        sliced: true,
        selected: true
      };
    } else {
      response['percentage'][index] = [verdict, (num_runs / total) * 100];
    }
  }
  return response;
};

OmegaupGraph.prototype.normalizePeriodRunCounts = function(stats, period) {
  var runs = this.groupRuns(stats.runs, period);
  var response = {categories: Object.keys(runs), delta: [], cumulative: []};
  var verdicts = ['AC', 'PA', 'WA', 'TLE', 'RTE'];
  for (var [index, verdict] of verdicts.entries()) {
    runs[verdict] = 0;
  }
  for (var [index, verdict] of verdicts.entries()) {
    response.delta[index] = {name: verdict, data: []};
    response.cumulative[index] = {name: verdict, data: []};
    for (var [ind, date] of response.categories.entries()) {
      runs[verdict] += parseInt(runs[date][verdict]);
      response.delta[index]['data'][ind] = parseInt(runs[date][verdict]);
      response.cumulative[index]['data'][ind] = runs[verdict];
    }
  }
  return response;
};

OmegaupGraph.prototype.countRuns = function(stats) {
  var total = 0;
  for (var runs of stats) {
    total += parseInt(runs['runs']);
  }
  return total;
};

OmegaupGraph.prototype.groupRuns = function(stats, prop) {
  return stats.reduce(function(groups, item) {
    var val = item[prop];
    groups[val] =
        groups[val] ||
        {WA: 0, PA: 0, AC: 0, TLE: 0, MLE: 0, OLE: 0, RTE: 0, CE: 0, JE: 0};
    if (item.verdict == 'WA') groups[val].WA += parseInt(item.runs);
    if (item.verdict == 'PA') groups[val].PA += parseInt(item.runs);
    if (item.verdict == 'AC') groups[val].AC += parseInt(item.runs);
    if (item.verdict == 'TLE') groups[val].TLE += parseInt(item.runs);
    if (item.verdict == 'MLE') groups[val].MLE += parseInt(item.runs);
    if (item.verdict == 'OLE') groups[val].OLE += parseInt(item.runs);
    if (item.verdict == 'RTE') groups[val].RTE += parseInt(item.runs);
    if (item.verdict == 'CE') groups[val].CE += parseInt(item.runs);
    if (item.verdict == 'JE') groups[val].JE += parseInt(item.runs);
    return groups;
  }, {});
};

OmegaupGraph.prototype.pendingRuns = function(refreshRate, updateStatsFn) {
  return new Highcharts.Chart({
    chart: {
      type: 'spline',
      animation: Highcharts.svg,  // don't animate in old IE
      marginRight: 10,
      renderTo: 'pending-runs-chart',
      events: {
        load: function() {
          // set up the updating of the chart each second
          var series = this.series[0];
          setInterval(function() {
            var x = (new Date()).getTime(),  // current time
                y = updateStatsFn();
            series.addPoint([x, y], true, true);
          }, refreshRate);
        }
      }
    },
    title: {text: 'Envíos aun no revisados'},
    xAxis: {type: 'datetime', tickPixelInterval: 200},
    yAxis: {
      title: {text: 'Total'},
      plotLines: [{value: 0, width: 1, color: '#808080'}]
    },
    tooltip: {
      formatter: function() {
        return '<b>' + this.series.name + '</b><br/>' +
               Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) + '<br/>' +
               Highcharts.numberFormat(this.y, 2);
      }
    },
    legend: {enabled: false},
    exporting: {enabled: false},
    series: [
      {
        name: 'Runs pendientes',
        data: (function() {
          // generate an array of random data
          var data = [], time = (new Date()).getTime(), i;

          for (i = -5; i <= 0; i++) {
            data.push({x: time + i * 1000, y: 0});
          }
          return data;
        })()
      }
    ]
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
    chart: {type: 'column', renderTo: renderTo},
    title: {text: 'Distribución de puntajes del concurso ' + title},
    xAxis: {
      categories: categories_vals,
      title: {text: 'Distribución de puntos en 100 intervalos'},
      labels: {
        formatter: function() {
          if (this.value % 10 == 0) {
            return this.value;
          } else {
            return '';
          }
        }
      }
    },
    yAxis: {min: 0, title: {text: '# Concursantes'}},
    tooltip: {},
    plotOptions: {column: {pointPadding: 0.2, borderWidth: 0}},
    series:
        [{name: 'Número de concursantes', data: this.getDistribution(stats)}]
  });
};

var oGraph = new OmegaupGraph();
