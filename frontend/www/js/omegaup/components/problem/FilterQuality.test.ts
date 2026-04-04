import { mount } from '@vue/test-utils';

import T from '../../lang';

import problem_FilterQuality from './FilterQuality.vue';

describe('FilterQuality.vue', () => {
  it('Should handle empty difficulty text', async () => {
    const wrapper = mount(problem_FilterQuality);

    expect(wrapper.text()).toContain(T.qualityFormQualityAny);
    expect(wrapper.text()).toContain(T.qualityFormQualityOnly);
    expect(wrapper.find('input[value="all"]').exists()).toBe(true);
    expect(wrapper.find('input[value="onlyQualityProblems"]').exists()).toBe(
      true,
    );
    const onlyQualityRadio = wrapper.find('input[value="onlyQualityProblems"]')
      .element as HTMLInputElement;
    const anyProblemRadio = wrapper.find('input[value="all"]')
      .element as HTMLInputElement;
    expect(onlyQualityRadio.checked).toBeTruthy();
    expect(anyProblemRadio.checked).toBeFalsy();
    await wrapper.find('input[value="all"]').trigger('click');
    expect(onlyQualityRadio.checked).toBeFalsy();
    expect(anyProblemRadio.checked).toBeTruthy();
  });
});
