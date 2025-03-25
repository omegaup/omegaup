import { mount } from '@vue/test-utils';

import T from '../../lang';

import problem_FilterDifficulty from './FilterDifficulty.vue';

describe('FilterDifficulty.vue', () => {
  it('Should handle empty difficulty text', async () => {
    const wrapper = mount(problem_FilterDifficulty, {
      propsData: {
        selectedDifficulty: null,
      },
    });

    expect(wrapper.text()).toContain(T.qualityFormDifficultyAny);
    expect(wrapper.text()).toContain(T.qualityFormDifficultyEasy);
    expect(wrapper.text()).toContain(T.qualityFormDifficultyMedium);
    expect(wrapper.text()).toContain(T.qualityFormDifficultyHard);
    expect(wrapper.find('input[value="all"]').exists()).toBe(true);
    expect(wrapper.find('input[value="easy"]').exists()).toBe(true);
    expect(wrapper.find('input[value="medium"]').exists()).toBe(true);
    expect(wrapper.find('input[value="hard"]').exists()).toBe(true);
  });
});
