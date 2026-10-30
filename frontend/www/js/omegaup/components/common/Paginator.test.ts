import { types } from '../../api_types';
import { shallowMount } from '@vue/test-utils';
import Vue, { ComponentOptions } from 'vue';

import common_Paginator from './Paginator.vue';

describe('Paginator.vue', () => {
  const pagerItems: types.PageItem[] = [
    {
      class: 'disabled',
      label: '«',
      page: 0,
    },
    {
      class: 'active',
      label: '1',
      page: 1,
    },
    {
      class: '',
      label: '»',
      page: 2,
    },
  ];

  it('Should handle pager items', async () => {
    const wrapper = shallowMount(common_Paginator as ComponentOptions<Vue>, {
      propsData: {
        pagerItems,
      },
    });
    expect(wrapper.text()).toContain(pagerItems[0].label);
    expect(wrapper.text()).toContain(pagerItems[1].label);
    expect(wrapper.text()).toContain(pagerItems[2].label);

    const links = wrapper.findAll('a');
    await links.at(2).trigger('click');
    expect(wrapper.emitted('page-changed')).toEqual([[2]]);
  });
});
