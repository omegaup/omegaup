import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import arena_Arena from './Arena.vue';

describe('Arena.vue', () => {
  it('Should handle details for a contest', () => {
    const wrapper = shallowMount(arena_Arena, {
      propsData: {
        title: 'Hello omegaUp',
        activeTab: 'problems',
      },
    });

    expect(wrapper.find('.clock').text()).toBe('∞');
    expect(wrapper.find('.socket-status-error').text()).toBe('✗');
    expect(wrapper.find('div[data-arena-wrapper]>div>h2>span').text()).toBe(
      'Hello omegaUp',
    );
  });

  it('Should mark as read clarifications tab', async () => {
    const wrapper = shallowMount(arena_Arena, {
      propsData: {
        title: 'Hello omegaUp',
        activeTab: 'problems',
        clarifications: [
          {
            answer: 'Si',
            author: 'user',
            clarification_id: 1,
            message: 'hello',
            problem_alias: 'problem',
            public: false,
            time: new Date(0),
          },
        ] as types.Clarification[],
      },
    });

    expect(
      wrapper.find('a[aria-controls="clarifications"] > span').classes(),
    ).toContain('unread');
    await wrapper.find('a[aria-controls="clarifications"]').trigger('click');
    expect(
      wrapper.find('a[aria-controls="clarifications"] > span').classes(),
    ).not.toContain('unread');
  });
});
