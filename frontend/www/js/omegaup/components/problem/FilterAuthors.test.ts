import { mount } from '@vue/test-utils';

import type { types } from '../../api_types';
import T from '../../lang';

import problem_FilterAuthors from './FilterAuthors.vue';

describe('FilterAuthors.vue', () => {
  it('Should handle list of authors', async () => {
    const wrapper = mount(problem_FilterAuthors, {
      propsData: {
        authors: {
          total: 1,
          ranking: [
            {
              author_score: 90,
              name: 'User 1',
              username: 'user1',
              classname: 'user-rank-master',
            },
            {
              author_score: 100,
              name: 'User 2',
              username: 'user2',
              classname: 'user-rank-master',
            },
          ],
        } as types.AuthorsRank,
      },
    });

    expect(wrapper.text()).toContain(T.problemCollectionAuthors);

    expect(wrapper.find('input[value="user1"]').exists()).toBe(true);
    expect(wrapper.find('input[value="user2"]').exists()).toBe(true);

    const checkboxInput1 = wrapper.find('input[value="user1"]')
      .element as HTMLInputElement;
    const checkboxInput2 = wrapper.find('input[value="user2"]')
      .element as HTMLInputElement;

    checkboxInput1.click();
    expect(checkboxInput1.checked).toBeTruthy();
    expect(checkboxInput2.checked).toBeFalsy();

    checkboxInput1.click();
    checkboxInput2.click();
    expect(checkboxInput1.checked).toBeFalsy();
    expect(checkboxInput2.checked).toBeTruthy();
  });
});
