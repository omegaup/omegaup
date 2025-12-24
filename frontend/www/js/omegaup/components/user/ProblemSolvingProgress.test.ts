import { mount } from '@vue/test-utils';
import ProblemSolvingProgress from './ProblemSolvingProgress.vue';

describe('ProblemSolvingProgress.vue', () => {
  const defaultProps = {
    solved: 50,
    attempting: 10,
    difficulty: {
      easy: 20,
      medium: 15,
      hard: 10,
      unlabelled: 5,
    },
  };

  it('should render the component with correct solved count', () => {
    const wrapper = mount(ProblemSolvingProgress, {
      propsData: defaultProps,
    });

    expect(wrapper.find('.solved-count').text()).toBe('50');
    expect(wrapper.find('.solved-label').exists()).toBe(true);
  });

  it('should render all difficulty cards', () => {
    const wrapper = mount(ProblemSolvingProgress, {
      propsData: defaultProps,
    });

    const cards = wrapper.findAll('.difficulty-card');
    expect(cards.length).toBe(4); // Easy, Medium, Hard, Unlabelled
  });

  it('should display correct difficulty counts', () => {
    const wrapper = mount(ProblemSolvingProgress, {
      propsData: defaultProps,
    });

    const counts = wrapper.findAll('.difficulty-count');
    expect(counts.at(0).text()).toBe('20'); // Easy
    expect(counts.at(1).text()).toBe('15'); // Medium
    expect(counts.at(2).text()).toBe('10'); // Hard
    expect(counts.at(3).text()).toBe('5'); // Unlabelled
  });

  it('should show attempting count when greater than 0', () => {
    const wrapper = mount(ProblemSolvingProgress, {
      propsData: defaultProps,
    });

    expect(wrapper.find('.attempting-label').exists()).toBe(true);
    expect(wrapper.find('.attempting-label').text()).toContain('10');
  });

  it('should not show attempting count when 0', () => {
    const wrapper = mount(ProblemSolvingProgress, {
      propsData: {
        ...defaultProps,
        attempting: 0,
      },
    });

    expect(wrapper.find('.attempting-label').exists()).toBe(false);
  });

  it('should render SVG circular chart', () => {
    const wrapper = mount(ProblemSolvingProgress, {
      propsData: defaultProps,
    });

    expect(wrapper.find('.circular-chart').exists()).toBe(true);
    expect(wrapper.findAll('.circle-segment').length).toBe(4);
  });

  it('should handle zero solved problems gracefully', () => {
    const wrapper = mount(ProblemSolvingProgress, {
      propsData: {
        solved: 0,
        attempting: 0,
        difficulty: {
          easy: 0,
          medium: 0,
          hard: 0,
          unlabelled: 0,
        },
      },
    });

    expect(wrapper.find('.solved-count').text()).toBe('0');
    expect(wrapper.find('.attempting-label').exists()).toBe(false);
  });
});
