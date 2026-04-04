import { shallowMount } from '@vue/test-utils';
import T from '../lang';

import omegaup_RadioSwitch from './RadioSwitch.vue';

describe('RadioSwitch.vue', () => {
  it('Should render a simple radio switch with default descriptions', () => {
    const wrapper = shallowMount(omegaup_RadioSwitch, {
      propsData: {
        selectedValue: true,
      },
    });
    expect(wrapper.text()).toContain(T.wordsYes);
    expect(wrapper.text()).toContain(T.wordsNo);
  });

  it('Should render a simple radio switch with custom descriptions', () => {
    const wrapper = shallowMount(omegaup_RadioSwitch, {
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
