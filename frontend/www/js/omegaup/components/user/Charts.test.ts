import { shallowMount, Wrapper } from '@vue/test-utils';
import Vue from 'vue';
import Charts from './Charts.vue';
import { omegaup } from '../../omegaup';
import T from '../../lang';

jest.mock('highcharts-vue', () => ({
  Chart: {
    name: 'highcharts',
    props: ['options', 'updateArgs'],
    render(h: Vue.CreateElement) {
      return h('div', { class: 'highcharts-mock' });
    },
  },
}));

function makeRun(date: string, verdict: string, runs: number): omegaup.RunInfo {
  return { date, verdict, runs };
}

const sampleData: omegaup.RunInfo[] = [
  makeRun('2024-01-15', 'AC', 5),
  makeRun('2024-01-15', 'WA', 2),
  makeRun('2024-01-16', 'AC', 3),
  makeRun('2024-01-16', 'TLE', 1),
  makeRun('2024-02-01', 'PA', 4),
  makeRun('2024-02-01', 'RTE', 1),
  makeRun('2024-03-10', 'CE', 2),
  makeRun('2024-03-10', 'AC', 6),
];

function freshData(): omegaup.RunInfo[] {
  return JSON.parse(JSON.stringify(sampleData));
}

function mountComponent(
  data: omegaup.RunInfo[] = [],
  username = 'testuser',
): Wrapper<Vue> {
  return shallowMount(Charts, {
    propsData: { data, username },
  });
}

describe('Charts.vue', () => {
  // Group 1: Rendering & Defaults
  describe('Rendering & Defaults', () => {
    it('mounts with empty data without crashing', () => {
      const wrapper = mountComponent([]);
      expect(wrapper.findComponent({ name: 'highcharts' }).exists()).toBe(true);
    });

    it('mounts with sample data', () => {
      const wrapper = mountComponent(freshData());
      expect(wrapper.findComponent({ name: 'highcharts' }).exists()).toBe(true);
    });

    it('has correct default state', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      expect(vm.type).toBe('delta');
      expect(vm.period).toBe('day');
    });
  });

  // Group 2: Conditional Rendering
  describe('Conditional Rendering', () => {
    it('shows period selectors when type is delta', () => {
      const wrapper = mountComponent(freshData());
      expect(wrapper.find('.period-group').exists()).toBe(true);
    });

    it('hides period selectors when type is total', async () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      vm.type = 'total';
      await Vue.nextTick();
      expect(wrapper.find('.period-group').exists()).toBe(false);
    });

    it('hides period selectors when type is empty string', async () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      vm.type = '';
      await Vue.nextTick();
      expect(wrapper.find('.period-group').exists()).toBe(false);
    });

    it('renders pie chart options when type is total, column otherwise', async () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;

      // Default type='delta' → column chart
      let chart = wrapper.findComponent({ name: 'highcharts' });
      expect(chart.props('options').chart.type).toBe('column');

      // Switch to total → pie chart
      vm.type = 'total';
      await Vue.nextTick();
      chart = wrapper.findComponent({ name: 'highcharts' });
      expect(chart.props('options').chart.type).toBe('pie');
    });
  });

  // Group 3: totalRuns
  describe('totalRuns', () => {
    it('returns 0 for empty data', () => {
      const wrapper = mountComponent([]);
      expect((wrapper.vm as any).totalRuns).toBe(0);
    });

    it('returns correct sum for sample data', () => {
      const wrapper = mountComponent(freshData());
      // 5+2+3+1+4+1+2+6 = 24
      expect((wrapper.vm as any).totalRuns).toBe(24);
    });

    it('returns the run count for a single entry', () => {
      const wrapper = mountComponent([makeRun('2024-01-01', 'AC', 7)]);
      expect((wrapper.vm as any).totalRuns).toBe(7);
    });
  });

  // Group 4: normalizedRunCounts — Pie Chart Data
  describe('normalizedRunCounts', () => {
    it('contains all 10 verdict keys', () => {
      const wrapper = mountComponent(freshData());
      const counts: Array<{ name: string; y: number }> = (wrapper.vm as any)
        .normalizedRunCounts;
      const names = counts.map((c) => c.name);
      for (const v of [
        'WA',
        'PA',
        'AC',
        'TLE',
        'MLE',
        'OLE',
        'RTE',
        'CE',
        'JE',
        'VE',
      ]) {
        expect(names).toContain(v);
      }
    });

    it('correctly aggregates counts per verdict', () => {
      const wrapper = mountComponent(freshData());
      const counts: Array<{ name: string; y: number }> = (wrapper.vm as any)
        .normalizedRunCounts;
      const byName: Record<string, number> = {};
      for (const c of counts) byName[c.name] = c.y;

      expect(byName['AC']).toBe(14);
      expect(byName['WA']).toBe(2);
      expect(byName['TLE']).toBe(1);
      expect(byName['PA']).toBe(4);
      expect(byName['RTE']).toBe(1);
      expect(byName['CE']).toBe(2);
      expect(byName['MLE']).toBe(0);
      expect(byName['OLE']).toBe(0);
      expect(byName['JE']).toBe(0);
      expect(byName['VE']).toBe(0);
    });

    it('marks AC entry with sliced and selected', () => {
      const wrapper = mountComponent(freshData());
      const counts: Array<{
        name: string;
        y: number;
        sliced?: boolean;
        selected?: boolean;
      }> = (wrapper.vm as any).normalizedRunCounts;
      const ac = counts.find((c) => c.name === 'AC');
      expect(ac).toBeDefined();
      expect(ac?.sliced).toBe(true);
      expect(ac?.selected).toBe(true);
    });

    it('does not mark non-AC entries with sliced or selected', () => {
      const wrapper = mountComponent(freshData());
      const counts: Array<{
        name: string;
        y: number;
        sliced?: boolean;
        selected?: boolean;
      }> = (wrapper.vm as any).normalizedRunCounts;
      const nonAc = counts.filter((c) => c.name !== 'AC');
      for (const entry of nonAc) {
        expect(entry.sliced).toBeUndefined();
        expect(entry.selected).toBeUndefined();
      }
    });
  });

  // Group 5: groupedPeriods — Date Bucketing
  describe('groupedPeriods', () => {
    it('groups by month using YYYY-MM substring', () => {
      const wrapper = mountComponent(freshData());
      const periods = (wrapper.vm as any).groupedPeriods;
      const monthKeys = Object.keys(periods.month);
      expect(monthKeys).toContain('2024-01');
      expect(monthKeys).toContain('2024-02');
      expect(monthKeys).toContain('2024-03');
    });

    it('groups by year using YYYY substring', () => {
      const wrapper = mountComponent(freshData());
      const periods = (wrapper.vm as any).groupedPeriods;
      const yearKeys = Object.keys(periods.year);
      expect(yearKeys).toContain('2024');
    });

    it('groups by day using locale strings different from raw date', () => {
      const wrapper = mountComponent(freshData());
      const periods = (wrapper.vm as any).groupedPeriods;
      const dayKeys = Object.keys(periods.day);
      expect(dayKeys.length).toBeGreaterThan(0);
      // Day keys should be locale-formatted, not raw YYYY-MM-DD
      for (const key of dayKeys) {
        expect(key).not.toMatch(/^\d{4}-\d{2}-\d{2}$/);
      }
    });

    it('groups by week with keys containing " - " separator', () => {
      const wrapper = mountComponent(freshData());
      const periods = (wrapper.vm as any).groupedPeriods;
      const weekKeys = Object.keys(periods.week);
      expect(weekKeys.length).toBeGreaterThan(0);
      for (const key of weekKeys) {
        expect(key).toContain(' - ');
      }
    });

    // Known Bug 1: groupedPeriods mutates props.data in-place (lines 227, 234–251).
    // stats = this.data is a reference; .day/.week/.month/.year are written onto
    // each prop object, violating Vue's one-way data flow.
    // TODO(#9750): Remove .skip once the bug is fixed.
    it.skip('[Bug 1] groupedPeriods should not mutate prop data objects', () => {
      const data = freshData();
      const wrapper = mountComponent(data);
      // Access groupedPeriods to trigger computation
      (wrapper.vm as any).groupedPeriods;

      // Props should remain unmodified after accessing a computed property
      expect(data[0]).not.toHaveProperty('day');
      expect(data[0]).not.toHaveProperty('week');
      expect(data[0]).not.toHaveProperty('month');
      expect(data[0]).not.toHaveProperty('year');
    });
  });

  // Group 6: normalizedPeriodRunCounts — Stacked Column Data
  describe('normalizedPeriodRunCounts', () => {
    it('has 5 verdict series per period', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      vm.period = 'month';
      const counts = vm.normalizedPeriodRunCounts;
      const expectedVerdicts = ['AC', 'PA', 'WA', 'TLE', 'RTE'];

      for (const p of ['day', 'week', 'month', 'year'] as const) {
        expect(counts[p].delta.length).toBe(5);
        expect(counts[p].cumulative.length).toBe(5);
        expect(counts[p].delta.map((s: any) => s.name)).toEqual(
          expectedVerdicts,
        );
        expect(counts[p].cumulative.map((s: any) => s.name)).toEqual(
          expectedVerdicts,
        );
      }
    });

    it('delta data matches raw grouped counts for month', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      const counts = vm.normalizedPeriodRunCounts;
      const monthDelta = counts.month.delta;

      // month categories: '2024-01', '2024-02', '2024-03'
      // AC: [8, 0, 6], PA: [0, 4, 0], WA: [2, 0, 0], TLE: [1, 0, 0], RTE: [0, 1, 0]
      const acSeries = monthDelta.find((s: any) => s.name === 'AC');
      expect(acSeries.data).toEqual([8, 0, 6]);

      const paSeries = monthDelta.find((s: any) => s.name === 'PA');
      expect(paSeries.data).toEqual([0, 4, 0]);

      const waSeries = monthDelta.find((s: any) => s.name === 'WA');
      expect(waSeries.data).toEqual([2, 0, 0]);

      const tleSeries = monthDelta.find((s: any) => s.name === 'TLE');
      expect(tleSeries.data).toEqual([1, 0, 0]);

      const rteSeries = monthDelta.find((s: any) => s.name === 'RTE');
      expect(rteSeries.data).toEqual([0, 1, 0]);
    });

    it('cumulative data is running sum of deltas for month', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      const counts = vm.normalizedPeriodRunCounts;
      const monthDelta = counts.month.delta;
      const monthCumulative = counts.month.cumulative;

      for (let i = 0; i < monthDelta.length; i++) {
        const delta = monthDelta[i].data;
        const cumulative = monthCumulative[i].data;
        let runningSum = 0;
        for (let j = 0; j < delta.length; j++) {
          runningSum += delta[j];
          expect(cumulative[j]).toBe(runningSum);
        }
      }
    });

    it('month categories match groupedPeriods month keys', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      const grouped = vm.groupedPeriods;
      const counts = vm.normalizedPeriodRunCounts;
      expect(counts.month.categories).toEqual(Object.keys(grouped.month));
    });

    // Known Bug 2: normalizedPeriodRunCounts aliases module-level singletons
    // (emptyPeriodRunCount, emptyGroupedPeriods) by reference at lines 197–198.
    // Two instances share the same object, so B's computation overwrites A's
    // cached result. Fix: clone the objects before use.
    // TODO(#9779): Remove .skip once the bug is fixed.
    it.skip('[Bug 2] each instance should have isolated normalizedPeriodRunCounts', () => {
      // Mount instance A with sample data
      const dataA = freshData();
      const wrapperA = mountComponent(dataA);
      const vmA = wrapperA.vm as any;
      const countsA = vmA.normalizedPeriodRunCounts;
      const savedCategoriesA = [...countsA.month.categories];
      expect(savedCategoriesA).toEqual(['2024-01', '2024-02', '2024-03']);

      // Mount instance B with completely different data
      const dataB = [
        makeRun('2025-06-01', 'AC', 10),
        makeRun('2025-07-01', 'WA', 3),
      ];
      const wrapperB = mountComponent(dataB);
      const vmB = wrapperB.vm as any;
      const countsB = vmB.normalizedPeriodRunCounts;

      // Each instance should return its own isolated object
      expect(countsA).not.toBe(countsB);

      // A's data should be preserved after B computes
      expect(countsA.month.categories).toEqual(savedCategoriesA);

      // B should have its own correct categories
      expect(countsB.month.categories).toEqual(['2025-06', '2025-07']);

      wrapperA.destroy();
      wrapperB.destroy();
    });

    // Known Bug 3: groupedPeriods only initializes {WA, PA, AC, TLE, RTE} at
    // line 263, so verdicts like CE/JE/MLE/OLE/VE produce NaN via
    // undefined + N at line 264. Fix: initialize all 10 verdicts, or guard
    // against missing keys.
    // TODO(#9751): Remove .skip once the bug is fixed.
    it.skip('[Bug 3] groupedPeriods should produce numeric counts for all verdicts', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      const grouped = vm.groupedPeriods;

      // CE runs should be aggregated as a number, not NaN
      expect(grouped.month['2024-03']['CE']).toBe(2);
    });
  });

  // Group 7: runsForPeriod — Type-Driven Selection
  describe('runsForPeriod', () => {
    it('returns delta series for type=delta and period=month', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      vm.period = 'month';
      const runs = vm.runsForPeriod;
      const counts = vm.normalizedPeriodRunCounts;
      expect(runs).toEqual(counts.month.delta);
    });

    it('returns cumulative series for type=cumulative and period=month', async () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      vm.period = 'month';
      vm.type = 'cumulative';
      await Vue.nextTick();
      const runs = vm.runsForPeriod;
      const counts = vm.normalizedPeriodRunCounts;
      expect(runs).toEqual(counts.month.cumulative);
    });
  });

  // Group 8: Highcharts Options & Tooltip Formatters
  describe('Highcharts Options & Tooltip Formatters', () => {
    it('periodStatisticOptions title contains username', () => {
      const wrapper = mountComponent(freshData(), 'alice');
      const vm = wrapper.vm as any;
      expect(vm.periodStatisticOptions.title.text).toContain('alice');
    });

    it('periodStatisticOptions chart type is column', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      expect(vm.periodStatisticOptions.chart.type).toBe('column');
    });

    it('periodStatisticOptions series maps from runsForPeriod with type column', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      const options = vm.periodStatisticOptions;
      const runsForPeriod = vm.runsForPeriod;

      expect(options.series.length).toBe(runsForPeriod.length);
      for (const series of options.series) {
        expect(series.type).toBe('column');
      }
    });

    it('aggregateStatisticOptions chart type is pie', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      expect(vm.aggregateStatisticOptions.chart.type).toBe('pie');
    });

    it('column tooltip formatter translates known verdict codes', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      const formatter = vm.periodStatisticOptions.tooltip.formatter;

      const context = {
        series: { name: 'AC' },
        x: '2024-01',
        y: 5,
        point: { stackTotal: 10 },
      };
      const result = formatter.call(context);
      expect(result).toContain(T.verdictAC);
      expect(result).toContain('5');
      expect(result).toContain('10');
    });

    it('pie tooltip formatter uses point.name for verdict lookup', () => {
      const wrapper = mountComponent(freshData());
      const vm = wrapper.vm as any;
      const formatter = vm.aggregateStatisticOptions.tooltip.formatter;

      const context = {
        point: { name: 'WA' },
        y: 3,
      };
      const result = formatter.call(context);
      expect(result).toContain(T.verdictWA);
      expect(result).toContain('3');
    });
  });
});
