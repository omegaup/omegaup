import { createLocalVue, shallowMount } from '@vue/test-utils';

import StatementTab from './StatementTab.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import Vue from 'vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('StatementTab.vue', () => {
  it('Should contain all 4 inputs', async () => {
    const wrapper = shallowMount(StatementTab, {
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
});
