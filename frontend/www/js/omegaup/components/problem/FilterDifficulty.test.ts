import { mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import problem_FilterDifficulty from './FilterDifficulty.vue';

describe('FilterDifficulty.vue', () => {
  it('Should handle empty difficulty text', async () => {
    const wrapper = mount(problem_FilterDifficulty);

    expect(wrapper.text()).toContain(T.qualityFormDifficultyEasy);
    expect(wrapper.text()).toContain(T.qualityFormDifficultyMedium);
    expect(wrapper.text()).toContain(T.qualityFormDifficultyHard);
  });
});
