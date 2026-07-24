import common_Stats from '../components/common/Stats.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import { types } from '../api_types';
import * as ui from '../ui';
import * as Highcharts from 'highcharts';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.StatsPayload();
  const callStatsApiTimeout = 10 * 1000;
  const updatePendingRunsChartTimeout = callStatsApiTimeout / 2;

  const pointsDistributionLabel =
    payload.entity_type === 'contest'
      ? T.wordsPointsDistribution
      : T.wordsPointsDistributionProblem;

  const getStats = (entityType: string): void => {
    if (entityType === 'contest') {
      api.Contest.stats({ contest_alias: payload.alias })
        .then((s) => Vue.set(statsChart, 'stats', s))
        .catch(ui.apiError);
    } else {
      api.Problem.stats({ problem_alias: payload.alias })
        .then((s) => Vue.set(statsChart, 'stats', s))
        .catch(ui.apiError);
    }
  };

  const normalizeRunCounts = (stats: types.StatsPayload) => {
    const result = [];
    for (const verdict in stats.verdict_counts) {
      if (!Object.prototype.hasOwnProperty.call(stats.verdict_counts, verdict))
        continue;
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
  };

  const getDistribution = (stats: types.StatsPayload) => {
    const distribution: number[] = [];
    if (stats.distribution) {
      for (const val in stats.distribution) {
        distribution.push(stats.distribution[val]);
      }
    }

    return distribution;
  };

  const getCategories = (stats: types.StatsPayload) => {
    const categoriesDistributionValues: { [key: number]: number } = {};
    let startOfBucket = 0;
    if (stats.distribution && stats.size_of_bucket) {
      for (const val in stats.distribution) {
        categoriesDistributionValues[val] = startOfBucket;
        startOfBucket += stats.size_of_bucket;
      }
    }
    return categoriesDistributionValues;
  };

  const statsChart = new Vue({
    el: '#main-container',
    components: {
      'omegaup-common-stats': common_Stats,
    },
    data: () => ({
      stats: payload,
      verdictChartOptions: {
        chart: {
          plotBackgroundColor: null,
          plotBorderWidth: null,
          plotShadow: false,
        },
        title: {
          text: ui.formatString(T.wordsVerdictsOf, {
            alias: payload.alias,
          }),
        },
        plotOptions: {
          pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: true,
              color: '#000000',
              connectorColor: '#000000',
              format:
                '<b>{point.name}</b>: {point.percentage:.2f}% ({point.y})',
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
        time: {
          useUTC: true,
        },
      },
      distributionChartOptions: {
        chart: { type: 'column' },
        title: {
          text: ui.formatString(pointsDistributionLabel, {
            alias: payload.alias,
          }),
        },
        xAxis: {
          categories: getCategories(payload),
          title: { text: T.wordsPointsDistributionInIntervals },
          labels: {
            step: 10,
          },
        },
        yAxis: { min: 0, title: { text: T.wordsNumberOfContestants } },
        tooltip: {},
        plotOptions: { column: { pointPadding: 0.2, borderWidth: 0 } },
        series: [
          { name: T.wordsNumberOfContestants, data: getDistribution(payload) },
        ],
        time: {
          useUTC: true,
        },
      },
      pendingChartOptions: {
        chart: {
          type: 'spline',
          marginRight: 10,
          events: {
            load: (ev: Event): void => {
              // set up the updating of the chart each second
              const series = (ev.target as unknown as Highcharts.Chart)
                .series[0];
              setInterval(() => {
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
          format: '<b>{series.name}</b><br/>{point.x}<br/>{point.y}',
        },
        legend: { enabled: false },
        exporting: { enabled: false },
        series: [
          {
            name: T.wordsPendingRuns,
            data: (() => {
              // generate an array of random data
              const data = [];
              const time = new Date().getTime();

              for (let i = -5; i <= 0; i++) {
                data.push({ x: time + i * 1000, y: 0 });
              }
              return data;
            })(),
          },
        ],
        time: {
          useUTC: true,
        },
      },
    }),
    render: function (createElement) {
      return createElement('omegaup-common-stats', {
        props: {
          stats: this.stats,
          verdictChartOptions: this.verdictChartOptions,
          distributionChartOptions: this.distributionChartOptions,
          pendingChartOptions: this.pendingChartOptions,
        },
        on: {
          'update-series': (series: types.StatsPayload): void => {
            statsChart.verdictChartOptions.series[0].data =
              normalizeRunCounts(series);
            statsChart.distributionChartOptions.series[0].data =
              getDistribution(series);
            statsChart.distributionChartOptions.xAxis.categories =
              getCategories(series);
            statsChart.stats.pending_runs = series.pending_runs;
          },
        },
      });
    },
  });

  setInterval(() => getStats(payload.entity_type), callStatsApiTimeout);
});
