import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import T from '../lang';

import omegaup_RadioSwitch from './RadioSwitch.vue';

const RadioSwitch = omegaup_RadioSwitch as ComponentOptions<Vue>;

describe('RadioSwitch.vue', () => {
  it('Should render a simple radio switch with default descriptions', () => {
    const wrapper = shallowMount(RadioSwitch, {
      propsData: {
        selectedValue: true,
      },
    });
    expect(wrapper.text()).toContain(T.wordsYes);
    expect(wrapper.text()).toContain(T.wordsNo);
  });

  it('Should render a simple radio switch with custom descriptions', () => {
    const wrapper = shallowMount(RadioSwitch, {
      propsData: {
        selectedValue: true,
        textForTrue: 'Red',
        textForFalse: 'Yellow',
      },
    });
    expect(wrapper.text()).toContain('Red');
    expect(wrapper.text()).toContain('Yellow');
    expect(wrapper.text()).not.toContain(T.wordsYes);
    expect(wrapper.text()).not.toContain(T.wordsNo);
  });
});
