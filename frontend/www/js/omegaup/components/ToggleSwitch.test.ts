import { shallowMount } from '@vue/test-utils';

import omegaup_ToggleSwitch, { ToggleSwitchSize } from './ToggleSwitch.vue';

describe('ToggleSwitch.vue', () => {
  it('Should render a simple toggle switch with default description and size', () => {
    const wrapper = shallowMount(omegaup_ToggleSwitch, {
      propsData: {
        checkedValue: true,
      },
    });
    expect(wrapper.find('label[class*="large"]').text()).toBe('Check');
  });

  it('Should render a simple toggle switch with custom description and size', () => {
    const wrapper = shallowMount(omegaup_ToggleSwitch, {
      propsData: {
        checkedValue: true,
        textDescription: 'Are you happy?',
        size: ToggleSwitchSize.Small,
      },
    });
    expect(wrapper.find('label[class*="small"]').text()).toBe('Are you happy?');
  });
});
