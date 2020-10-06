import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import problem_List from './List.vue';

describe('List.vue', () => {
  it('Should handle empty list of problems', async () => {
    const wrapper = shallowMount(problem_List, {
      propsData: {
        isSysadmin: false,
        problems: [],
        pagerItems: [
          {
            class: 'disabled',
            label: '1',
            page: 1,
          },
        ],
      },
    });

    expect(wrapper.find('table tbody').text()).toBe('');
  });
});
