import { mount } from '@vue/test-utils';
import expect from 'expect';

import { types } from '../../api_types';
import T from '../../lang';

import problem_CollectionListAuthor from './CollectionListAuthor.vue';

describe('CollectionListAuthor.vue', () => {
  it('Should handle details of problem list by author collection', async () => {
    const wrapper = mount(problem_CollectionListAuthor, {
      propsData: {
        data: {
          authorsRanking: {
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
              {
                author_score: 95,
                name: 'User 3',
                username: 'user3',
                classname: 'user-rank-master',
              },
            ],
          } as types.AuthorsRank,
        } as types.CollectionDetailsByAuthorPayload,
        difficulty: 'all',
        selectedAuthors: <string[]>['user3'],
      },
    });

    expect(wrapper.text()).toContain(T.omegaupTitleCollectionsByAuthor);
    expect(wrapper.text()).toContain(T.problemCollectionAuthors);

    expect(wrapper.find('input[value="user1"]').exists()).toBe(true);
    expect(wrapper.find('input[value="user2"]').exists()).toBe(true);
    expect(wrapper.find('input[value="user3"]').exists()).toBe(true);

    const checkboxInput1 = <HTMLInputElement>(
      wrapper.find('input[value="user1"]').element
    );
    const checkboxInput2 = <HTMLInputElement>(
      wrapper.find('input[value="user2"]').element
    );
    const checkboxInput3 = <HTMLInputElement>(
      wrapper.find('input[value="user3"]').element
    );

    expect(checkboxInput1.checked).toBeFalsy();
    expect(checkboxInput2.checked).toBeFalsy();
    expect(checkboxInput3.checked).toBeTruthy();

    checkboxInput1.click();
    checkboxInput2.click();
    checkboxInput3.click();

    expect(checkboxInput1.checked).toBeTruthy();
    expect(checkboxInput2.checked).toBeTruthy();
    expect(checkboxInput3.checked).toBeFalsy();
  });
});
