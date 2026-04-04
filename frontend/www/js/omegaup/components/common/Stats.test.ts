import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import * as ui from '../../ui';

import common_Stats from './Stats.vue';

describe('Stats.vue', () => {
  const totalRuns = 167;
  const sampleStats = {
    stats: {
      cases_stats: {
        'easy.00': 217,
        'easy.01': 179,
        'medium.00': 181,
        'medium.01': 181,
        sample: 221,
      },
      pending_runs: [],
      status: 'ok',
      total_runs: totalRuns,
      verdict_counts: {
        AC: 92,
        CE: 3,
        JE: 0,
        MLE: 0,
        'NO-AC': 0,
        OLE: 0,
        PA: 38,
        RFE: 0,
        RTE: 0,
        TLE: 0,
        VE: 0,
        WA: 34,
      },
    },
    verdictChartOptions: {
      chart: {
        plotBackgroundColor: null,
        pltBorderWidth: null,
        plotShadow: false,
      },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'ponter',
          dataLabels: {
            color: '#000000',
            connectorColor: '#000000',
            enabled: true,
            format: '<b>{point.name}</b>: {point.percentage:.2f}% ({point.y})',
          },
        },
      },
      series: [
        {
          data: [
            { name: 'AC', selected: true, sliced: true, y: 92 },
            ['PA', 38],
            ['WA', 34],
            ['TLE', 0],
            ['OLE', 0],
            ['MLE', 0],
            ['RTE', 0],
            ['RFE', 0],
            ['CE', 3],
            ['JE', 0],
            ['VE', 0],
          ],
          name: T.wordsProportion,
          type: 'pie',
        },
      ],
      time: { useUTC: true },
      title: { text: ui.formatString(T.wordsVerdictsOf, { alias: 'alias' }) },
    },
    pendingChartOptions: {
      chart: {
        marginRight: 10,
        type: 'spline',
      },
      exporting: { enabled: false },
      legend: { enabled: false },
      series: [
        {
          data: [
            { x: 1000, y: 0 },
            { x: 2000, y: 0 },
            { x: 3000, y: 0 },
            { x: 4000, y: 0 },
            { x: 5000, y: 0 },
            { x: 6000, y: 0 },
          ],
          name: T.wordsPendingRuns,
        },
      ],
      time: { useUTC: true },
      title: { text: T.wordsSubmissionsNotYetReviewed },
      tooltip: { format: '<b>{series.name}</b><br/>{point.x}<br/>{point.y}' },
      xAxis: { tickPixelInterval: 200, type: 'datetime' },
      yAxis: {
        plotLines: [{ color: '#808080', value: 0, width: 1 }],
        title: { text: T.wordsTotal },
      },
    },
    distributionChartOptions: {
      chart: { type: 'column' },
      plotOptions: { column: { borderWidth: 0, pointPadding: 0.2 } },
      series: [
        {
          data: [1, 0, 3, 0, 2, 1],
          name: T.wordsNumberOfContestants,
        },
      ],
      time: { useUTC: true },
      title: { text: T.wordsPointsDistribution },
      tooltip: {},
      xAxis: {
        categories: { 0: 0, 1: 0.2, 2: 0.4, 3: 0.6, 4: 0.8, 5: 1 },
        labels: { step: 10 },
        title: { text: T.wordsPointsDistribution },
      },
      yAxis: { min: 0, title: { text: T.wordsNumberOfContestants } },
    },
  };

  it('Should handle contest or problem stats', async () => {
    const wrapper = shallowMount(common_Stats, {
      propsData: sampleStats,
    });

    expect(wrapper.find('.copy h1').text()).toBe(T.liveStatistics);
    expect(wrapper.find('.copy .total-runs').text()).toBe(
      ui.formatString(T.totalRuns, { numRuns: totalRuns }),
    );
  });
});
