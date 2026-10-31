import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import omegaup_ToggleSwitch, { ToggleSwitchSize } from './ToggleSwitch.vue';

const ToggleSwitch = omegaup_ToggleSwitch as ComponentOptions<Vue>;

describe('ToggleSwitch.vue', () => {
  it('Should render a simple toggle switch with default description and size', () => {
    const wrapper = shallowMount(ToggleSwitch, {
      propsData: {
        checkedValue: true,
      },
    });
    expect(wrapper.find('label[class*="large"]').text()).toBe('Check');
  });

  it('Should render a simple toggle switch with custom description and size', () => {
    const wrapper = shallowMount(ToggleSwitch, {
      propsData: {
        checkedValue: true,
        textDescription: 'Are you happy?',
        size: ToggleSwitchSize.Small,
      },
    });
    expect(wrapper.find('label[class*="small"]').text()).toBe('Are you happy?');
  });
});
