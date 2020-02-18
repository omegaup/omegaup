import { OmegaUp, T } from '../omegaup.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('runs-chart-payload').innerText,
  );

  const minY = payload.total.length === 0 ? 0 : payload.total[0] / 2.0;

  window.chart = new Highcharts.Chart({
    chart: {
      type: 'area',
      renderTo: 'runs-chart',
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
  });
});
