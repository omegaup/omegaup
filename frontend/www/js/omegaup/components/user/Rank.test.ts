import { shallowMount, mount } from '@vue/test-utils';

import T from '../../lang';

import user_Rank from './Rank.vue';

describe('Rank.vue', () => {
  const propsData = {
    page: 1,
    length: 10,
    isIndex: true,
    isLogged: true,
    availableFilters: {},
    filter: '',
    ranking: [],
    resultTotal: 0,
    pagerItems: [
      {
        class: 'disabled',
        label: '1',
        page: 1,
      },
    ],
    searchResultUsers: [],
  };

  it('Should handle an empty rank', () => {
    const wrapper = shallowMount(user_Rank, {
      propsData,
    });

    expect(wrapper.find('table').exists()).toBeFalsy;
    expect(wrapper.find('div.empty-category').text()).toBe(T.userRankEmptyList);
  });

  it('Should handle a rank with data', () => {
    const wrapper = mount(user_Rank, {
      propsData: {
        ...propsData,
        ...{
          ranking: [
            {
              rank: 1,
              country: 'mx',
              username: 'user',
              name: 'User Name',
              classname: '',
              score: 234,
              problems_solved: 500,
            },
          ],
        },
      },
    });

    expect(wrapper.find('table tbody').text()).toContain('user');
    expect(wrapper.find('table tbody').text()).toContain('234');
  });
});
