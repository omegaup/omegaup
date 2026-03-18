import { createLocalVue, shallowMount } from '@vue/test-utils';

import MultipleCasesInput from './MultipleCasesInput.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import Vue from 'vue';
import store from '@/js/omegaup/problem/creator/store';
import { Group } from '@/js/omegaup/problem/creator/types';
import { v4 as uuid } from 'uuid';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

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

    const inputElements = wrapper.findAll('[label]');

    expect(inputElements.length).toBe(expectedTextInputText.length);

    inputElements.wrappers.forEach((element, index) => {
      expect(element.attributes('label')).toBe(expectedTextInputText[index]); // We need to make it like this because that's how Vue-Bootstrap input element works
    });

    // Check if the name is being generated correctly

    await wrapper.setData({ multipleCasesPrefix: 'case#' });

    await Vue.nextTick();

    expect(wrapper.find('[description]').attributes('description')).toContain(
      'case',
    ); // Again, the description is stored inside the attribute
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

    const formSelect = wrapper.find(
      'b-form-select-stub[name="multiple-cases-group"]',
    );
    expect(formSelect.props()['options']).toBe(wrapper.vm.options);
  });
});
