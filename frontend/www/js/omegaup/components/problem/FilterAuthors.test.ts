import { mount } from '@vue/test-utils';
import expect from 'expect';

import { types } from '../../api_types';
import T from '../../lang';

import problem_FilterAuthors from './FilterAuthors.vue';

describe('Filter.vue', () => {
  it('Should handle empty list of authors', async () => {
    const wrapper = mount(problem_FilterAuthors, {
      propsData: {
        authors: {
            total: 1,
            ranking: [
              { author_score: 90, name: 'User', username: 'user', classname: 'user-rank-master' },
              { author_score: 100, name: 'User 2', username: 'user2', classname: 'user-rank-master' },
            ],
          } as types.AuthorsRank,
      },
    });

    expect(wrapper.text()).toContain(T.problemCollectionAuthors);

    expect(wrapper.find('input[value="user"]').exists()).toBe(true);
    expect(wrapper.find('input[value="user2"]').exists()).toBe(true);
  });
});
