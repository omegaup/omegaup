import { mount } from '@vue/test-utils';

import problem_FinderWizard from './FinderWizard.vue';

describe('FinderWizard.vue', () => {
  const propsData = {
    possibleTags: [] as { name: string }[],
  };

  it('Should not emit close on Escape when the wizard is hidden', async () => {
    const wrapper = mount(problem_FinderWizard, {
      propsData: { ...propsData, show: false },
    });

    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('close')).toBeUndefined();

    wrapper.destroy();
  });

  it('Should emit close on Escape when the wizard is shown', async () => {
    const wrapper = mount(problem_FinderWizard, {
      propsData: { ...propsData, show: true },
    });

    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('close')).toHaveLength(1);

    wrapper.destroy();
  });
});
