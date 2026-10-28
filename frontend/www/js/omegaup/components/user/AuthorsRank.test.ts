import { shallowMount, mount } from '@vue/test-utils';
import Vue, { ComponentOptions } from 'vue';
import user_AuthorsRank from './AuthorsRank.vue';
import { types } from '../../api_types';

const AuthorsRank = user_AuthorsRank as ComponentOptions<Vue>;

describe('AuthorsRank.vue', () => {
  const defaultProps = {
    page: 1,
    length: 10,
    rankingData: {
      ranking: [
        {
          author_ranking: 1,
          country_id: 'mx',
          username: 'author1',
          name: 'Author One',
          classname: 'user-rank-master',
          author_score: 98.765,
        },
      ],
      total: 1,
    } as types.AuthorsRank,
    pagerItems: [
      {
        class: 'disabled',
        label: '1',
        page: 1,
      },
    ] as types.PageItem[],
    searchResultUsers: [] as types.ListItem[],
  };

  it('Should render authors ranking table with correct data', () => {
    const wrapper = mount(AuthorsRank, {
      propsData: defaultProps,
    });

    expect(wrapper.find('table tbody').text()).toContain('author1');
    expect(wrapper.find('table tbody').text()).toContain('Author One');
    expect(wrapper.find('table tbody').text()).toContain('98.77');
  });

  it('Should emit update-search-result-users when typeahead updates', async () => {
    const wrapper = shallowMount(AuthorsRank, {
      propsData: defaultProps,
    });

    const typeahead = wrapper.findComponent({ name: 'common-typeahead' });
    if (typeahead.exists()) {
      typeahead.vm.$emit('update-existing-options', 'testuser');
      expect(wrapper.emitted('update-search-result-users')).toBeTruthy();
      expect(wrapper.emitted('update-search-result-users')?.[0]).toEqual([
        'testuser',
      ]);
    }
  });
});
