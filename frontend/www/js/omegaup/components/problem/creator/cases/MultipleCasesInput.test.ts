import { createLocalVue, shallowMount } from '@vue/test-utils';

import MultipleCasesInput from './MultipleCasesInput.vue';
import T from '../../../../lang';
import Vue from 'vue';
import store from '@/js/omegaup/problem/creator/store';
import { Group } from '@/js/omegaup/problem/creator/types';
import { v4 as uuid } from 'uuid';

const localVue = createLocalVue();

const testGroup: Group = {
  groupID: uuid(),
  name: 'omegaup',
  points: 100,
  autoPoints: false,
  ungroupedCase: false,
  cases: [],
};
store.commit('casesStore/addGroup', testGroup);

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

    const inputElements = wrapper.findAll('label');

    expect(inputElements.length).toBe(expectedTextInputText.length);

    inputElements.wrappers.forEach((element, index) => {
      expect(element.text()).toBe(expectedTextInputText[index]);
    });

    // Check if the name is being generated correctly

    await wrapper.setData({ multipleCasesPrefix: 'case#' });

    await Vue.nextTick();

    expect(wrapper.find('small.form-text').text()).toContain('case');
  });

  it('Should handle autoformatting', () => {
    const wrapper = shallowMount(MultipleCasesInput, {
      localVue,
      store,
    });

    // These any are necessary since wrapper.vm doesn't load the component's methods to typescript, even if they exist
    const invalidString = 'INVALID STRING234 !@#!@#';
    const result = (wrapper.vm as any).formatter(invalidString);
    expect(result).toBe('invalidstring234');

    const invalidNumber = -2;
    const numberResult = (wrapper.vm as any).numberFormatter(invalidNumber);
    expect(numberResult).toBe(1);
  });

  it('Should handle choice of groups', () => {
    const wrapper = shallowMount(MultipleCasesInput, {
      localVue,
      store,
    });

    const formSelect = wrapper.find('select[name="multiple-cases-group"]');
    const optionValues = formSelect
      .findAll('option')
      .wrappers.map((option) => option.attributes('value'));
    expect(optionValues).toEqual(
      wrapper.vm.options.map((option) => String(option.value)),
    );
  });
});
