import { createLocalVue, shallowMount } from '@vue/test-utils';

import MultipleCasesInput from './MultipleCasesInput.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import Vue from 'vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('MultipleCasesInput.vue', () => {
  it('Should contain all 4 inputs', async () => {
    const wrapper = shallowMount(MultipleCasesInput, {
      localVue,
    });

    const expectedTextInputText = [
      T.problemCreatorPrefix,
      T.problemCreatorSuffix,
      T.problemCreatorNumberOfCases,
      T.problemCreatorGroupName,
    ];

    await Vue.nextTick();

    const inputElements = wrapper.findAll('[label]');

    expect(inputElements.length).toBe(expectedTextInputText.length);

    inputElements.wrappers.forEach((element, index) => {
      expect(element.attributes('label')).toBe(expectedTextInputText[index]); // We need to make it like this because that's how Vue-Bootstrap input element works
    });

    // Check if the name is being generated correctly

    await wrapper.setData({ multipleCasesPrefix: 'case#' });

    await Vue.nextTick();

    expect(wrapper.find('[description]').attributes('description')).toContain(
      'case#',
    ); // Again, the description is stored inside the attribute
  });
});
