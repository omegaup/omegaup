import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import TagsSolvedChart from './TagsSolvedChart.vue';

// Mock Highcharts to avoid actual chart rendering in tests
// Use render function instead of template since Vue runtime-only build
// doesn't include the template compiler
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

  it('should render the component with chart title', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
      },
    });

    expect(wrapper.find('.chart-title').exists()).toBe(true);
  });

  it('should render chart container when tags are provided', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
      },
    });

    expect(wrapper.find('.chart-container').exists()).toBe(true);
    expect(wrapper.find('.no-data').exists()).toBe(false);
  });

  it('should show no-data message when tags array is empty', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: [],
      },
    });

    expect(wrapper.find('.no-data').exists()).toBe(true);
    expect(wrapper.find('.chart-container').exists()).toBe(false);
  });

  it('should compute correct chart options', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
      },
    });

    const vm = wrapper.vm as any;
    const options = vm.chartOptions;

    expect(options.chart.type).toBe('pie');
    expect(options.plotOptions.pie.innerSize).toBe('60%');
    expect(options.series[0].data.length).toBe(5);
  });

  it('should limit chart data to 18 tags maximum', () => {
    const manyTags = Array.from({ length: 25 }, (_, i) => ({
      name: `tag-${i}`,
      count: 25 - i,
    }));

    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: manyTags,
      },
    });

    const vm = wrapper.vm as any;
    const options = vm.chartOptions;

    expect(options.series[0].data.length).toBe(18);
  });

  it('should have correct data structure for chart', () => {
    const wrapper = shallowMount(TagsSolvedChart, {
      propsData: {
        tags: defaultTags,
      },
    });

    const vm = wrapper.vm as any;
    const options = vm.chartOptions;
    const firstDataPoint = options.series[0].data[0];

    expect(firstDataPoint).toHaveProperty('name', 'dynamic-programming');
    expect(firstDataPoint).toHaveProperty('y', 25);
    expect(firstDataPoint).toHaveProperty('color');
  });
});
