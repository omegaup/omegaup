import common_RunsChart from '../components/common/RunsChart.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('runs-chart-payload').innerText,
  );

  if (payload.total.length === 0) return;

  const minY = payload.total[0] - payload.total[0] / 2.0;

  const commonRunsChart = new Vue({
    el: '#runs-chart',
    render: function(createElement) {
      return createElement('omegaup-common-runschart', {
        props: {
          chartOptions: this.chartOptions,
        },
      });
    },
    data: {
      chartOptions: {
        chart: {
          type: 'area',
          height: 300,
          spacingTop: 20,
        },
        title: { text: T.wordsTotalRuns },
        xAxis: {
          type: 'datetime',
          title: { text: null },
          categories: payload.date.reverse(),
        },
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
            data: payload.total.reverse(),
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
      },
    },
    components: {
      'omegaup-common-runschart': common_RunsChart,
    },
  });
});
