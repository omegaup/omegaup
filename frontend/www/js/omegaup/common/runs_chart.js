import common_RunsChart from '../components/common/RunsChart.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let commonRunsChart = new Vue({
    el: '#runs-chart',
    render: function(createElement) {
      return createElement('omegaup-common-runschart', {
        props: {
          chartOptions: this.chartOptions,
        },
      });
    },
    data: {
      chartOptions: {},
    },
    components: {
      'omegaup-common-runschart': common_RunsChart,
    },
  });

  API.Run.counts()
    .then(series => {
      if (series.total.length === 0) return;

      let dataInSeries = [];
      let acInSeries = [];
      for (let i in series.total) {
        if (series.total.hasOwnProperty(i)) {
          dataInSeries.push(parseInt(series.total[i]));
        }
        if (series.ac.hasOwnProperty(i)) {
          acInSeries.push(parseInt(series.ac[i]));
        }
      }

      let minDate = new Date();
      minDate.setDate(minDate.getDate() - 30 * 3);

      let minY = dataInSeries[0] - dataInSeries[0] * 0.5;

      commonRunsChart.chartOptions = {
        chart: {
          type: 'area',
          height: 300,
          spacingTop: 20,
        },
        title: { text: T.wordsTotalRuns },
        xAxis: { type: 'datetime', title: { text: null } },
        yAxis: { title: { text: T.wordsRuns }, min: minY },
        legend: { enabled: false },
        plotOptions: {
          area: {
            lineWidth: 1,
            marker: { enabled: false },
            shadow: false,
            states: { hover: { lineWidth: 1 } },
            threshold: null,
          },
        },
        series: [
          {
            type: 'area',
            name: T.wordsRuns,
            pointInterval: 24 * 3600 * 1000,
            pointStart: minDate.getTime(),
            data: dataInSeries.reverse(),
            fillColor: {
              linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
              stops: [
                [0, Highcharts.getOptions().colors[0]],
                [
                  1,
                  Highcharts.Color(Highcharts.getOptions().colors[0])
                    .setOpacity(0)
                    .get('rgba'),
                ],
              ],
            },
          },
        ],
      };
    })
    .fail(UI.apiError);
});
