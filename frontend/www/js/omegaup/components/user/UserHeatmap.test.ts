import { shallowMount } from '@vue/test-utils';
import UserHeatmap from './UserHeatmap.vue';

// Mock Highcharts to avoid actual chart rendering in tests
// NOTE: jest.mock() is hoisted above all variable declarations by babel-jest,
// so all mock setup must be self-contained inside the factory functions.

jest.mock('highcharts/highstock', () => {
  const chart = { destroy: jest.fn() };
  const Chart = jest.fn().mockImplementation(() => chart);
  return {
    __esModule: true,
    default: { Chart },
    Chart,
  };
});

jest.mock('highcharts/modules/heatmap', () => jest.fn());

jest.mock('../../user/profile', () => ({
  getHeatmapChartOptions: jest.fn().mockReturnValue({
    chart: { renderTo: null, type: 'heatmap' },
  }),
}));

describe('UserHeatmap.vue', () => {
  const sampleData = [
    { date: '2024-01-01', runs: 3 },
    { date: '2024-01-02', runs: 5 },
    { date: '2024-01-03', runs: 2 },
    { date: '2024-01-10', runs: 4 },
    { date: '2024-06-15', runs: 1 },
  ];

  const defaultProps = {
    username: 'testuser',
    availableYears: [2024, 2023],
    isLoading: false,
    data: sampleData,
  };

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('should mount and render the container', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    expect(wrapper.find('.user-heatmap-container').exists()).toBe(true);
    expect(wrapper.find('.user-heatmap-wrapper').exists()).toBe(true);
  });

  it('should render stats elements', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    expect(wrapper.find('.heatmap-stats').exists()).toBe(true);
    expect(wrapper.find('.heatmap-primary').exists()).toBe(true);
    expect(wrapper.find('.heatmap-secondary').exists()).toBe(true);
  });

  it('should render year selector with all available years', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    const options = wrapper.findAll('option');
    expect(options.length).toBe(2);
    expect(options.at(0).attributes('value')).toBe('2024');
    expect(options.at(1).attributes('value')).toBe('2023');
  });

  it('should initialize selectedYear to the first available year', async () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    // Wait for the watcher to fire
    await wrapper.vm.$nextTick();

    const vm = wrapper.vm as any;
    expect(vm.selectedYear).toBe(2024);
  });

  it('should format date correctly with zero-padding', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    const vm = wrapper.vm as any;

    // Single-digit month and day
    expect(vm.formatDateToString(new Date(2024, 0, 5))).toBe('2024-01-05');

    // Double-digit month and day
    expect(vm.formatDateToString(new Date(2024, 10, 25))).toBe('2024-11-25');

    // December 31
    expect(vm.formatDateToString(new Date(2024, 11, 31))).toBe('2024-12-31');

    // Start of year
    expect(vm.formatDateToString(new Date(2024, 0, 1))).toBe('2024-01-01');
  });

  it('should compute setActivityStats correctly with known data', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    const vm = wrapper.vm as any;

    const dateMap = new Map<string, number>([
      ['2024-01-01', 3],
      ['2024-01-02', 5],
      ['2024-01-03', 2],
      ['2024-01-10', 4],
    ]);

    const start = new Date(2024, 0, 1);
    // Use a fixed "now" date well past our data
    const now = new Date(2024, 1, 1);

    vm.selectedYear = 2024;
    vm.setActivityStats(dateMap, start, now);

    expect(vm.totalSubmissions).toBe(14); // 3 + 5 + 2 + 4
    expect(vm.activeDays).toBe(4);
    expect(vm.maxStreak).toBe(3); // Jan 1, 2, 3 consecutive
  });

  it('should compute streak correctly when days are not consecutive', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    const vm = wrapper.vm as any;

    // Two separate streaks: Jan 5-6 (2 days) and Jan 10-12 (3 days)
    const dateMap = new Map<string, number>([
      ['2024-01-05', 1],
      ['2024-01-06', 2],
      ['2024-01-10', 1],
      ['2024-01-11', 3],
      ['2024-01-12', 1],
    ]);

    const start = new Date(2024, 0, 1);
    const now = new Date(2024, 1, 1);

    vm.selectedYear = 2024;
    vm.setActivityStats(dateMap, start, now);

    expect(vm.totalSubmissions).toBe(8); // 1+2+1+3+1
    expect(vm.activeDays).toBe(5);
    expect(vm.maxStreak).toBe(3); // Jan 10, 11, 12
  });

  it('should return all zeros for empty data in setActivityStats', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: {
        ...defaultProps,
        data: [],
      },
    });

    const vm = wrapper.vm as any;

    const dateMap = new Map<string, number>();
    const start = new Date(2024, 0, 1);
    const now = new Date(2024, 1, 1);

    vm.selectedYear = 2024;
    vm.setActivityStats(dateMap, start, now);

    expect(vm.totalSubmissions).toBe(0);
    expect(vm.activeDays).toBe(0);
    expect(vm.maxStreak).toBe(0);
  });

  it('should emit year-changed event when year is changed', async () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    const vm = wrapper.vm as any;
    vm.selectedYear = 2023;
    vm.onYearChange();

    expect(wrapper.emitted('year-changed')).toBeTruthy();
    expect(wrapper.emitted('year-changed')?.[0]).toEqual([2023]);
  });

  it('should set hasRendered to true after renderHeatmap', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    const vm = wrapper.vm as any;

    // Reset state so renderHeatmap proceeds
    vm.hasRendered = false;

    // Provide a mock container element (simulates the @Ref)
    Object.defineProperty(vm, 'heatmapContainer', {
      get: () => document.createElement('div'),
      configurable: true,
    });

    vm.renderHeatmap();

    expect(vm.hasRendered).toBe(true);
  });

  it('should skip renderHeatmap when hasRendered is true', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    const vm = wrapper.vm as any;
    vm.hasRendered = true;

    // Spy on setActivityStats to verify renderHeatmap does not proceed
    const spy = jest.spyOn(vm, 'setActivityStats');
    vm.renderHeatmap();

    expect(spy).not.toHaveBeenCalled();
    spy.mockRestore();
  });

  it('should render gracefully with empty data array', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: {
        ...defaultProps,
        data: [],
      },
    });

    expect(wrapper.find('.user-heatmap-container').exists()).toBe(true);

    const vm = wrapper.vm as any;
    // Stats should remain at default values
    expect(vm.totalSubmissions).toBe(0);
    expect(vm.activeDays).toBe(0);
    expect(vm.maxStreak).toBe(0);
  });

  it('should reset hasRendered when data watcher fires', async () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: defaultProps,
    });

    const vm = wrapper.vm as any;
    vm.hasRendered = true;

    // Spy on renderHeatmap to verify the watcher calls it
    const renderSpy = jest.spyOn(vm, 'renderHeatmap').mockImplementation(() => {
      // no-op to prevent actual rendering and hasRendered flip
    });

    // Trigger the data watcher by setting new data
    await wrapper.setProps({
      data: [{ date: '2024-03-01', runs: 7 }],
    });

    // The watcher synchronously sets hasRendered = false
    expect(vm.hasRendered).toBe(false);

    // And schedules renderHeatmap via $nextTick
    await wrapper.vm.$nextTick();
    expect(renderSpy).toHaveBeenCalled();

    renderSpy.mockRestore();
  });

  it('should handle single day of data correctly', () => {
    const wrapper = shallowMount(UserHeatmap, {
      propsData: {
        ...defaultProps,
        data: [{ date: '2024-07-15', runs: 10 }],
      },
    });

    const vm = wrapper.vm as any;

    const dateMap = new Map<string, number>([['2024-07-15', 10]]);

    const start = new Date(2024, 0, 1);
    const now = new Date(2024, 11, 31);

    vm.selectedYear = 2024;
    vm.setActivityStats(dateMap, start, now);

    expect(vm.totalSubmissions).toBe(10);
    expect(vm.activeDays).toBe(1);
    expect(vm.maxStreak).toBe(1);
  });
});
