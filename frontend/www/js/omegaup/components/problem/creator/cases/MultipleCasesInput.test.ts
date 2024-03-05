import { createLocalVue, shallowMount } from '@vue/test-utils';

import MultipleCasesInput from './MultipleCasesInput.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import Vue from 'vue';
import store from '@/js/omegaup/problem/creator/store';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('MultipleCasesInput.vue', () => {
  it('Should contain all 4 inputs', async () => {
    const wrapper = shallowMount(MultipleCasesInput, {
      localVue,
      store,
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

  it('Should handle autoformatting', () => {
    const wrapper = shallowMount(MultipleCasesInput, {
      localVue,
      store,
    });

    // These any are neccesary since wrapper.vm doesn't load the component's methods to typescript, even if they exist
    const invalidString = 'INVALID STRING234 !@#!@#';
    const result = (wrapper.vm as any).formatter(invalidString);
    expect(result).toBe('invalidstring234');

    const invalidNumber = -2;
    const numberResult = (wrapper.vm as any).numberFormatter(invalidNumber);
    expect(numberResult).toBe(0);
  });
});
