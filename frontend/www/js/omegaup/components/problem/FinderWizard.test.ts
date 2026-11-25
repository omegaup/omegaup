import { shallowMount } from '@vue/test-utils';

import finder_wizard from './FinderWizard.vue';

describe('FinderWizard.vue', () => {
  it('Should emit close on Escape when the wizard is visible', async () => {
    const wrapper = shallowMount(finder_wizard, {
      propsData: {
        possibleTags: [],
        show: true,
      },
    });

    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('close')).toBeTruthy();

    wrapper.destroy();
  });

  it('Should not emit close on Escape when the wizard is hidden', async () => {
    const wrapper = shallowMount(finder_wizard, {
      propsData: {
        possibleTags: [],
        show: false,
      },
    });

    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('close')).toBeFalsy();

    wrapper.destroy();
  });

  it('Should not emit close on Escape while focused inside an input field', async () => {
    const wrapper = shallowMount(finder_wizard, {
      propsData: {
        possibleTags: [],
        show: true,
      },
    });

    const input = document.createElement('input');
    document.body.appendChild(input);
    input.dispatchEvent(
      new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }),
    );
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('close')).toBeFalsy();

    document.body.removeChild(input);
    wrapper.destroy();
  });
});
