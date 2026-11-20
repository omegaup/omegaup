import { shallowMount } from '@vue/test-utils';
import Vue, { ComponentOptions } from 'vue';

import common_Confirmation from './Confirmation.vue';

describe('Confirmation.vue', () => {
  it('Should render questions and emit user actions', async () => {
    const wrapper = shallowMount(common_Confirmation as ComponentOptions<Vue>, {
      propsData: {
        question: 'Are you sure?',
        answerYes: 'Yes',
        answerNo: 'No',
      },
    });

    expect(wrapper.text()).toContain('Are you sure?');
    expect(wrapper.text()).toContain('Yes');
    expect(wrapper.text()).toContain('No');

    const buttons = wrapper.findAll('button');
    // close button
    await buttons.at(0).trigger('click');
    expect(wrapper.emitted('close')).toBeTruthy();

    // yes button
    await buttons.at(1).trigger('click');
    expect(wrapper.emitted('yes')).toBeTruthy();

    // no button
    await buttons.at(2).trigger('click');
    expect(wrapper.emitted('no')).toBeTruthy();
  });
});
