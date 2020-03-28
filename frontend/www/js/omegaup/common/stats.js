import common_Stats from '../components/common/Stats.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import API from '../api.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  Highcharts.setOptions({ global: { useUTC: false } });
  const payload = JSON.parse(document.getElementById('payload').innerText);
  const callStatsApiTimeout = 10 * 1000;
  const updatePendingRunsChartTimeout = callStatsApiTimeout / 2;

  const pointsDistributionLabel =
    payload.entity_type === 'contest'
      ? T.wordsPointsDistribution
      : T.wordsPointsDistributionProblem;
  const stats = {
    total_runs: 0,
    pending_runs: [],
    max_wait_time: 0,
    max_wait_time_guid: 0,
    verdict_counts: {},
    distribution: [],
    size_of_bucket: 10,
    total_points: 0,
  };

  let statsChart = new Vue({
    el: '#common-stats',
    render: function(createElement) {
      return createElement('omegaup-common-stats', {
        props: {
          stats: this.stats,
          verdictChartOptions: this.verdictChartOptions,
          distributionChartOptions: this.distributionChartOptions,
          pendingChartOptions: this.pendingChartOptions,
        },
        on: {
          'update-series': function(series) {
            statsChart.verdictChartOptions.series[0].data = normalizeRunCounts(
              series,
            );
            statsChart.distributionChartOptions.series[0].data = getDistribution(
              series,
            );
            statsChart.distributionChartOptions.xAxis.categories = getCategories(
              series,
            );
            statsChart.stats.pending_runs = series.pending_runs;
          },
        },
      });
    },
    data: {
      stats: payload,
      verdictChartOptions: {
        chart: {
          plotBackgroundColor: null,
          plotBorderWidth: null,
          plotShadow: false,
        },
        title: {
          text: UI.formatString(T.wordsVerdictsOf, {
            alias: payload.alias,
          }),
        },
        tooltip: {
          formatter() {
            return UI.formatString(T.wordsNumberOfRuns, {
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
              formatter() {
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
            name: T.wordsProportion,
            data: normalizeRunCounts(payload),
          },
        ],
      },
      distributionChartOptions: {
        chart: { type: 'column' },
        title: {
          text: UI.formatString(pointsDistributionLabel, {
            alias: payload.alias,
          }),
        },
        xAxis: {
          categories: getCategories(payload),
          title: { text: T.wordsPointsDistributionInIntervals },
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
        yAxis: { min: 0, title: { text: T.wordsNumberOfContestants } },
        tooltip: {},
        plotOptions: { column: { pointPadding: 0.2, borderWidth: 0 } },
        series: [
          { name: T.wordsNumberOfContestants, data: getDistribution(payload) },
        ],
      },
      pendingChartOptions: {
        chart: {
          type: 'spline',
          animation: Highcharts.svg, // don't animate in old IE
          marginRight: 10,
          events: {
            load: function() {
              // set up the updating of the chart each second
              const series = this.series[0];
              setInterval(function() {
                const x = new Date().getTime(), // current time
                  y = statsChart.stats.pending_runs.length;
                series.addPoint([x, y], true, true);
              }, updatePendingRunsChartTimeout);
            },
          },
        },
        title: { text: T.wordsSubmissionsNotYetReviewed },
        xAxis: { type: 'datetime', tickPixelInterval: 200 },
        yAxis: {
          title: { text: T.wordsTotal },
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
            name: T.wordsPendingRuns,
            data: (function() {
              // generate an array of random data
              let data = [],
                time = new Date().getTime(),
                i;

              for (i = -5; i <= 0; i++) {
                data.push({ x: time + i * 1000, y: 0 });
              }
              return data;
            })(),
          },
        ],
      },
    },
    components: {
      'omegaup-common-stats': common_Stats,
    },
  });

  function getStats(entityType) {
    if (entityType === 'contest') {
      API.Contest.stats({ contest_alias: payload.alias })
        .then(s => Vue.set(statsChart, 'stats', s))
        .catch(omegaup.UI.apiError);
    } else {
      API.Problem.stats({ problem_alias: payload.alias })
        .then(s => Vue.set(statsChart, 'stats', s))
        .catch(omegaup.UI.apiError);
    }
  }

  function normalizeRunCounts(stats) {
    let result = [];
    for (const verdict in stats.verdict_counts) {
      if (!stats.verdict_counts.hasOwnProperty(verdict)) continue;
      if (verdict === 'NO-AC') continue;
      if (verdict === 'AC') {
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
  }

  function getDistribution(stats) {
    const distribution = [];
    for (const val in stats.distribution) {
      distribution.push(parseInt(stats.distribution[val]));
    }

    return distribution;
  }

  function getCategories(stats) {
    const categoriesDistributionValues = [];
    let startOfBucket = 0;
    for (const val in stats.distribution) {
      categoriesDistributionValues[val] = startOfBucket;
      startOfBucket += stats.size_of_bucket;
    }
    return categoriesDistributionValues;
  }

  setInterval(() => getStats(payload.entity_type), callStatsApiTimeout);
});
