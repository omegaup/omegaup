import { createLocalVue, shallowMount } from '@vue/test-utils';

import CaseInput from './CaseInput.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import Vue from 'vue';
import store from '@/js/omegaup/problem/creator/store';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('CaseInput.vue', () => {
  it('Should contain all 4 inputs', async () => {
    const wrapper = shallowMount(CaseInput, {
      localVue,
      store,
    });

    const expectedTextInputText = [
      T.problemCreatorCaseName,
      T.problemCreatorGroupName,
      T.problemCreatorPoints,
      T.problemCreatorAutomaticPointsRecommended,
    ];

    await Vue.nextTick();

    const inputElements = wrapper.findAll('[label]');

    expect(inputElements.length).toBe(expectedTextInputText.length);

    inputElements.wrappers.forEach((element, index) => {
      expect(element.attributes('label')).toBe(expectedTextInputText[index]); // We need to make it like this because that's how Vue-Bootstrap input element works
    });
  });
  it('Should handle autoformatting', () => {
    const wrapper = shallowMount(CaseInput, {
      localVue,
      store,
    });

    // These any are necessary since wrapper.vm doesn't load the component's methods to typescript, even if they exist
    const invalidString = 'INVALID STRING234 !@#!@#';
    const result = (wrapper.vm as any).formatter(invalidString);
    expect(result).toBe('invalidstring234');

    const invalidNumber = -2;
    const numberResult = (wrapper.vm as any).pointsFormatter(invalidNumber);
    expect(numberResult).toBe(0);
  });
});
