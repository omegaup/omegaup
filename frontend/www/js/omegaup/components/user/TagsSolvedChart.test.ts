import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import TagsSolvedChart from './TagsSolvedChart.vue';

jest.mock('highcharts-vue', () => ({
  Chart: {
    name: 'highcharts',
    render(h: Vue.CreateElement) {
      return h('div', { class: 'highcharts-mock' });
    },
  },
}));

describe('TagsSolvedChart.vue', () => {
  const defaultTags = [
    { name: 'dynamic-programming', count: 25 },
    { name: 'greedy', count: 20 },
    { name: 'graphs', count: 15 },
    { name: 'binary-search', count: 10 },
    { name: 'math', count: 5 },
  ];

  const defaultTagsFull = [
    { name: 'dynamic-programming', count: 25 },
    { name: 'greedy', count: 20 },
    { name: 'graphs', count: 15 },
    { name: 'binary-search', count: 10 },
    { name: 'math', count: 5 },
    { name: 'strings', count: 4 },
    { name: 'geometry', count: 3 },
  ];

  it('should render the component with chart title', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    expect(wrapper.find('.chart-title').exists()).toBe(true);
  });

  it('should render chart container when tags are provided', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    expect(wrapper.find('.chart-container').exists()).toBe(true);
    expect(wrapper.find('.no-data').exists()).toBe(false);
  });

  it('should show no-data message when tags array is empty', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: [],
        tagsFull: [],
      },
    });

    expect(wrapper.find('.no-data').exists()).toBe(true);
    expect(wrapper.find('.chart-container').exists()).toBe(false);
  });

  it('should compute correct chart options', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    const vm = wrapper.vm as any;
    const options = vm.chartOptions;

    expect(options.chart.type).toBe('pie');
    expect(options.plotOptions.pie.innerSize).toBe('60%');
    expect(options.series[0].data.length).toBe(5);
  });

  it('should have correct data structure for chart', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    const vm = wrapper.vm as any;
    const options = vm.chartOptions;
    const firstDataPoint = options.series[0].data[0];

    expect(firstDataPoint).toHaveProperty('name', 'dynamic-programming');
    expect(firstDataPoint).toHaveProperty('y', 25);
    expect(firstDataPoint).toHaveProperty('color');
  });

  it('should render the chart by default and offer a table toggle', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    expect((wrapper.vm as any).view).toBe('chart');
    const buttons = wrapper.findAll('.btn-group button');
    expect(buttons.length).toBe(2);
  });

  it('should fall back to tags when tagsFull is missing', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
      },
    });

    const vm = wrapper.vm as any;
    expect(vm.sortedTagsFull.map((r: any) => r.name)).toEqual(
      defaultTags.map((t) => t.name),
    );
  });

  it('should use the full tag list in the table when tagsFull is provided', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    const vm = wrapper.vm as any;
    expect(vm.sortedTagsFull.length).toBe(defaultTagsFull.length);
    expect(vm.sortedTagsFull.map((r: any) => r.name)).toEqual(
      defaultTagsFull.map((t) => t.name),
    );
  });

  it('should sort the table by count descending by default', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    const vm = wrapper.vm as any;
    const counts = vm.sortedTagsFull.map((r: any) => r.count);
    const sorted = [...counts].sort((a, b) => b - a);
    expect(counts).toEqual(sorted);
  });

  it('should sort by tag name ascending when sortKey is name and direction is asc', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    const vm = wrapper.vm as any;
    vm.sortKey = 'name';
    vm.sortDirection = 'asc';
    const names = vm.sortedTagsFull.map((r: any) => r.name);
    const sorted = [...names].sort();
    expect(names).toEqual(sorted);
  });

  it('should flip sort direction when clicking the active sort column header', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    const vm = wrapper.vm as any;
    vm.sortKey = 'count';
    vm.sortDirection = 'desc';
    const before = vm.sortedTagsFull.map((r: any) => r.count);
    vm.toggleSort('count');
    expect(vm.sortDirection).toBe('asc');
    const after = vm.sortedTagsFull.map((r: any) => r.count);
    expect(after).toEqual([...before].reverse());
  });

  it('should switch to the table view and render its rows', async () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
        tagsFull: defaultTagsFull,
      },
    });

    const vm = wrapper.vm as any;
    vm.view = 'table';
    await Vue.nextTick();
    expect(vm.view).toBe('table');
    expect(wrapper.find('table.tags-table').exists()).toBe(true);
  });
});
