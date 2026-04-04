import { mount } from '@vue/test-utils';

import type { types } from '../../api_types';
import T from '../../lang';

import problem_CollectionListAuthor from './CollectionListAuthor.vue';

describe('CollectionListAuthor.vue', () => {
  it('Should handle details of problem list by author collection', async () => {
    const wrapper = mount(problem_CollectionListAuthor, {
      propsData: {
        data: {
          authorsRanking: {
            total: 3,
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
        difficulty: 'easy',
        selectedAuthors: ['user3'],
        problems: [
          {
            alias: 'Problem-1',
            title: 'Problem 1',
            difficulty: 4,
            difficulty_histogram: null,
            points: 100,
            problem_id: 1,
            quality: 4,
            quality_histogram: null,
            quality_seal: true,
            ratio: 0,
            score: 0,
            visibility: 2,
            tags: [
              {
                name: 'problemLevelBasicIntroductionToProgramming',
                source: 'owner',
              },
            ],
          },
        ],
      },
    });

    expect(wrapper.text()).toContain(T.omegaupTitleCollectionsByAuthor);
    expect(wrapper.text()).toContain(T.problemCollectionAuthors);
    expect(wrapper.text()).toContain(T.wordsDifficulty);
    expect(wrapper.text()).toContain('Problem 1');
    expect(wrapper.text()).not.toContain(T.courseAssignmentProblemsEmpty);

    expect(wrapper.find('input[value="user1"]').exists()).toBe(true);
    expect(wrapper.find('input[value="user2"]').exists()).toBe(true);
    expect(wrapper.find('input[value="user3"]').exists()).toBe(true);

    const checkboxInput1 = wrapper.find('input[value="user1"]')
      .element as HTMLInputElement;
    const checkboxInput2 = wrapper.find('input[value="user2"]')
      .element as HTMLInputElement;
    const checkboxInput3 = wrapper.find('input[value="user3"]')
      .element as HTMLInputElement;
    const radioInput1 = wrapper.find('input[value="all"]')
      .element as HTMLInputElement;
    const radioInput2 = wrapper.find('input[value="easy"]')
      .element as HTMLInputElement;

    expect(checkboxInput1.checked).toBeFalsy();
    expect(checkboxInput2.checked).toBeFalsy();
    expect(checkboxInput3.checked).toBeTruthy();
    expect(radioInput1.checked).toBeFalsy();
    expect(radioInput2.checked).toBeTruthy();

    checkboxInput1.click();
    checkboxInput2.click();
    checkboxInput3.click();
    radioInput1.click();

    expect(checkboxInput1.checked).toBeTruthy();
    expect(checkboxInput2.checked).toBeTruthy();
    expect(checkboxInput3.checked).toBeFalsy();
    expect(radioInput1.checked).toBeTruthy();
    expect(radioInput2.checked).toBeFalsy();
  });

  it('Should handle empty list of problems in author collection', async () => {
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
            ],
          } as types.AuthorsRank,
        } as types.CollectionDetailsByAuthorPayload,
        difficulty: 'easy',
      },
    });

    expect(wrapper.text()).toContain(T.omegaupTitleCollectionsByAuthor);
    expect(wrapper.text()).toContain(T.problemCollectionAuthors);
    expect(wrapper.text()).toContain(T.wordsDifficulty);
    expect(wrapper.text()).toContain(T.courseAssignmentProblemsEmpty);
  });
});
