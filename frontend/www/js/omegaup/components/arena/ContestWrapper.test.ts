import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import arena_ContestWrapper from './ContestWrapper.vue';

describe('ContestWrapper.vue', () => {
  it('Should handle details for a contest', () => {
    const wrapper = shallowMount(arena_ContestWrapper, {
      propsData: {
        contestTitle: 'Hello omegaUp',
        activeTab: 'problems',
      },
    });

    expect(wrapper.find('.clock').text()).toBe('∞');
    expect(wrapper.find('.socket-status').text()).toBe('✗');
    expect(wrapper.find('.title>h1>span').text()).toBe('Hello omegaUp');
  });
});
