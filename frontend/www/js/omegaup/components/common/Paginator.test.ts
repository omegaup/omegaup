import { types } from '../../api_types';
import { shallowMount } from '@vue/test-utils';

import common_Paginator from './Paginator.vue';

describe('Paginator.vue', () => {
  const pagerItems: types.PageItem[] = [
    {
      class: 'disabled',
      label: '«',
      page: 0,
    },
    {
      class: 'disabled',
      label: '1',
      page: 1,
    },
    {
      class: 'disabled',
      label: '»',
      page: 2,
    },
  ];

  it('Should handle pager items', async () => {
    const wrapper = shallowMount(common_Paginator, {
      propsData: {
        pagerItems,
      },
    });
    expect(wrapper.text()).toContain(pagerItems[0].label);
    expect(wrapper.text()).toContain(pagerItems[1].label);
    expect(wrapper.text()).toContain(pagerItems[2].label);
  });
});
