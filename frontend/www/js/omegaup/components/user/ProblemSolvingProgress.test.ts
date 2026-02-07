import { shallowMount } from '@vue/test-utils';
import ProblemSolvingProgress from './ProblemSolvingProgress.vue';

describe('ProblemSolvingProgress.vue', () => {
  const defaultDifficulty = {
    easy: 15,
    medium: 18,
    hard: 5,
    unlabelled: 4,
  };

  const circumference = 2 * Math.PI * 50; // r=50

  it('should render the component with chart title', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    expect(wrapper.find('.chart-title').exists()).toBe(true);
  });

  it('should render circular chart and difficulty breakdown', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    expect(wrapper.find('.circular-chart').exists()).toBe(true);
    expect(wrapper.find('.difficulty-list').exists()).toBe(true);
    expect(wrapper.findAll('.difficulty-item').length).toBe(4);
  });

  it('should calculate total correctly from difficulty breakdown', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;
    expect(vm.total).toBe(42); // 15 + 18 + 5 + 4
  });

  it('should display total in center when not hovering', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;
    expect(vm.displayCount).toBe(42);
  });

  it('should display attempting count when provided and not hovering', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
        attempting: 7,
      },
    });

    expect(wrapper.find('.attempting-label').exists()).toBe(true);
    expect(wrapper.find('.attempting-label').text()).toContain('7');
  });

  it('should not display attempting label when attempting is 0', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
        attempting: 0,
      },
    });

    expect(wrapper.find('.attempting-label').exists()).toBe(false);
  });

  it('should compute correct stroke dash arrays for easy segment', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;
    const easyPercent = 15 / 42;
    const expectedDash = `${easyPercent * circumference} ${circumference}`;
    expect(vm.easyDash).toBe(expectedDash);
  });

  it('should compute correct stroke dash offset for medium segment', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;
    const easyPercent = 15 / 42;
    const expectedOffset = -easyPercent * circumference;
    expect(vm.mediumOffset).toBeCloseTo(expectedOffset);
  });

  it('should compute correct stroke dash offset for hard segment', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;
    const prevPercent = (15 + 18) / 42;
    const expectedOffset = -prevPercent * circumference;
    expect(vm.hardOffset).toBeCloseTo(expectedOffset);
  });

  it('should compute correct stroke dash offset for unlabelled segment', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;
    const prevPercent = (15 + 18 + 5) / 42;
    const expectedOffset = -prevPercent * circumference;
    expect(vm.unlabelledOffset).toBeCloseTo(expectedOffset);
  });

  it('should handle zero values gracefully', () => {
    const zeroDifficulty = {
      easy: 0,
      medium: 0,
      hard: 0,
      unlabelled: 0,
    };

    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: zeroDifficulty,
      },
    });

    const vm = wrapper.vm as any;
    expect(vm.total).toBe(0);
    expect(vm.displayCount).toBe(0);
    expect(vm.easyDash).toBe(`0 ${circumference}`);
    expect(vm.mediumOffset).toBe(0);
    expect(vm.hardOffset).toBe(0);
    expect(vm.unlabelledOffset).toBe(0);
  });

  it('should update display count on hover', async () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;

    // Initially shows total
    expect(vm.displayCount).toBe(42);

    // Simulate hovering on easy segment
    vm.hoveredSegment = 'easy';
    expect(vm.displayCount).toBe(15);

    // Simulate hovering on medium segment
    vm.hoveredSegment = 'medium';
    expect(vm.displayCount).toBe(18);

    // Simulate hovering on hard segment
    vm.hoveredSegment = 'hard';
    expect(vm.displayCount).toBe(5);

    // Simulate hovering on unlabelled segment
    vm.hoveredSegment = 'unlabelled';
    expect(vm.displayCount).toBe(4);

    // Leave hover
    vm.hoveredSegment = null;
    expect(vm.displayCount).toBe(42);
  });

  it('should apply correct color styles on hover', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;

    // Not hovering - no color style
    expect(vm.hoveredCountStyle).toEqual({});

    // Hover on easy - green
    vm.hoveredSegment = 'easy';
    expect(vm.hoveredCountStyle).toEqual({
      color: 'var(--problem-progress-easy-color)',
    });

    // Hover on medium - yellow
    vm.hoveredSegment = 'medium';
    expect(vm.hoveredCountStyle).toEqual({
      color: 'var(--problem-progress-medium-color)',
    });

    // Hover on hard - red
    vm.hoveredSegment = 'hard';
    expect(vm.hoveredCountStyle).toEqual({
      color: 'var(--problem-progress-hard-color)',
    });

    // Hover on unlabelled - gray
    vm.hoveredSegment = 'unlabelled';
    expect(vm.hoveredCountStyle).toEqual({
      color: 'var(--problem-progress-unlabelled-color)',
    });
  });

  it('should display hover label with correct text', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    const vm = wrapper.vm as any;

    // Not hovering - empty label
    expect(vm.hoveredLabel).toBe('');

    // Hover states have labels (we just check they're not empty)
    vm.hoveredSegment = 'easy';
    expect(vm.hoveredLabel).toBeTruthy();

    vm.hoveredSegment = 'medium';
    expect(vm.hoveredLabel).toBeTruthy();
  });

  it('should hide attempting label when hovering', async () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
        attempting: 7,
      },
    });

    // Initially visible
    expect(wrapper.find('.attempting-label').exists()).toBe(true);

    // Set hover state
    await wrapper.setData({ hoveredSegment: 'easy' });

    // Should be hidden now
    expect(wrapper.find('.attempting-label').exists()).toBe(false);
  });

  it('should render all four circle segments', () => {
    const wrapper = shallowMount(ProblemSolvingProgress, {
      propsData: {
        difficulty: defaultDifficulty,
      },
    });

    expect(wrapper.find('.circle-segment.easy').exists()).toBe(true);
    expect(wrapper.find('.circle-segment.medium').exists()).toBe(true);
    expect(wrapper.find('.circle-segment.hard').exists()).toBe(true);
    expect(wrapper.find('.circle-segment.unlabelled').exists()).toBe(true);
  });
});
