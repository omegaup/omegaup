import { mount } from '@vue/test-utils';
import expect from 'expect';

import problem_FilterDifficulty from './FilterDifficulty.vue';

describe('FilterDifficulty.vue', () => {
  it('Should handle empty difficulty text', async () => {
    const wrapper = mount(problem_FilterDifficulty, {
      propsData: {
        selectedDifficulty: null,
      },
    });

    expect(
      wrapper.find('input[value="qualityFormDifficultyEasy').exists(),
    ).toBe(true);
    expect(
      wrapper.find('input[value="qualityFormDifficultyMedium').exists(),
    ).toBe(true);
    expect(
      wrapper.find('input[value="qualityFormDifficultyHard').exists(),
    ).toBe(true);
  });
});
