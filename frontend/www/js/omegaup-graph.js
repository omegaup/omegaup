function OmegaupGraph() {
  var self = this;
}

OmegaupGraph.prototype.verdictCounts = function(renderTo, title, stats) {
  return new Highcharts.Chart({
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      renderTo: renderTo
    },
    title: { text: 'veredictos de ' + title },
    tooltip: {
      formatter: function() {
        return '<b>Envíos</b>: ' + stats.verdict_counts[this.point.name];
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
            return '<b>' +
              this.point.name +
              '</b>: ' +
              this.percentage.toFixed(2) +
              ' % (' +
              stats.verdict_counts[this.point.name] +
              ')';
          }
        }
      }
    },
    series: [
      {
        type: 'pie',
        name: 'Proporción',
        data: this.normalizeRunCounts(stats)
      }
    ]
  });
};

OmegaupGraph.prototype.normalizeRunCounts = function(stats) {
  return [
    ['WA', stats.verdict_counts['WA'] / stats.total_runs * 100],
    ['PA', stats.verdict_counts['PA'] / stats.total_runs * 100],
    {
      name: 'AC',
      y: stats.verdict_counts['AC'] / stats.total_runs * 100,
      sliced: true,
      selected: true
    },
    ['TLE', stats.verdict_counts['TLE'] / stats.total_runs * 100],
    ['MLE', stats.verdict_counts['MLE'] / stats.total_runs * 100],
    ['OLE', stats.verdict_counts['OLE'] / stats.total_runs * 100],
    ['RTE', stats.verdict_counts['RTE'] / stats.total_runs * 100],
    ['CE', stats.verdict_counts['CE'] / stats.total_runs * 100],
    ['JE', stats.verdict_counts['JE'] / stats.total_runs * 100]
  ];
};

OmegaupGraph.prototype.pendingRuns = function(refreshRate, updateStatsFn) {
  return new Highcharts.Chart({
    chart: {
      type: 'spline',
      animation: Highcharts.svg,
      // don't animate in old IE
      marginRight: 10,
      renderTo: 'pending-runs-chart',
      events: {
        load: function() {
          // set up the updating of the chart each second
          var series = this.series[0];
          setInterval(
            function() {
              var x = new Date().getTime(),
                // current time
                y = updateStatsFn();
              series.addPoint([x, y], true, true);
            },
            refreshRate
          );
        }
      }
    },
    title: { text: 'Envíos aun no revisados' },
    xAxis: { type: 'datetime', tickPixelInterval: 200 },
    yAxis: {
      title: { text: 'Total' },
      plotLines: [{ value: 0, width: 1, color: '#808080' }]
    },
    tooltip: {
      formatter: function() {
        return '<b>' +
          this.series.name +
          '</b><br/>' +
          Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) +
          '<br/>' +
          Highcharts.numberFormat(this.y, 2);
      }
    },
    legend: { enabled: false },
    exporting: { enabled: false },
    series: [
      {
        name: 'Runs pendientes',
        data: (function() {
          // generate an array of random data
          var data = [], time = new Date().getTime(), i;

          for (i = -5; i <= 0; i++) {
            data.push({ x: time + i * 1000, y: 0 });
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
    chart: { type: 'column', renderTo: renderTo },
    title: { text: 'Distribución de puntajes del concurso ' + title },
    xAxis: {
      categories: categories_vals,
      title: { text: 'Distribución de puntos en 100 intervalos' },
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
    yAxis: { min: 0, title: { text: '# Concursantes' } },
    tooltip: {},
    plotOptions: { column: { pointPadding: 0.2, borderWidth: 0 } },
    series: [
      { name: 'Número de concursantes', data: this.getDistribution(stats) }
    ]
  });
};

var oGraph = new OmegaupGraph();
