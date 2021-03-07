import { shallowMount } from '@vue/test-utils';

import arena_Arena from './Arena.vue';

describe('Arena.vue', () => {
  it('Should handle details for a contest', () => {
    const wrapper = shallowMount(arena_Arena, {
      propsData: {
        contestTitle: 'Hello omegaUp',
        activeTab: 'problems',
      },
    });

    expect(wrapper.find('.clock').text()).toBe('∞');
    expect(wrapper.find('.socket-status-error').text()).toBe('✗');
    expect(wrapper.find('div[data-arena-wrapper]>div>h2>span').text()).toBe(
      'Hello omegaUp',
    );
  });
});
