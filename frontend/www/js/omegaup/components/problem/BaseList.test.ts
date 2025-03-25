import { shallowMount } from '@vue/test-utils';

import problem_BaseList from './BaseList.vue';

describe('BaseList.vue', () => {
  it('Should handle empty list of problems', async () => {
    const wrapper = shallowMount(problem_BaseList, {
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
