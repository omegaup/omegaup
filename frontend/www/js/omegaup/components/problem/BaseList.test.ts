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

  it.each([
    {
      selectedTags: ['problemTagArrays'],
      excludedTags: ['problemTagLoops', 'problemTagConditionals'],
      expected:
        '?tag[]=problemTagArrays&tag[]=problemTagMatrices&excluded_tag[]=problemTagLoops&excluded_tag[]=problemTagConditionals',
    },
    {
      selectedTags: undefined,
      excludedTags: ['problemTagLoops'],
      expected: '?tag[]=problemTagMatrices&excluded_tag[]=problemTagLoops',
    },
    {
      selectedTags: [],
      excludedTags: [],
      expected: '?tag[]=problemTagMatrices',
    },
  ])(
    'Should build tag links that preserve excluded tags',
    ({ selectedTags, excludedTags, expected }) => {
      const wrapper = shallowMount(problem_BaseList, {
        propsData: {
          problems: [
            {
              alias: 'problem',
              title: 'Problem',
              difficulty: 1,
              points: 1,
              problem_id: 1,
              quality: 1,
              ratio: 0,
              visibility: 2,
              tags: [{ name: 'problemTagMatrices', source: 'owner' }],
            },
          ],
          selectedTags,
          excludedTags,
          path: '/problem/collection/problemLevelBasic/',
        },
      });

      expect(wrapper.find('tbody a.badge').attributes('href')).toBe(
        `/problem/collection/problemLevelBasic/${expected}`,
      );
    },
  );

  it('Should not keep a clicked tag in the excluded tags', () => {
    const wrapper = shallowMount(problem_BaseList, {
      propsData: {
        problems: [
          {
            alias: 'problem',
            title: 'Problem',
            difficulty: 1,
            points: 1,
            problem_id: 1,
            quality: 1,
            ratio: 0,
            visibility: 2,
            tags: [{ name: 'problemTagMatrices', source: 'owner' }],
          },
        ],
        selectedTags: [],
        excludedTags: ['problemTagMatrices', 'problemTagLoops'],
        path: '/problem/collection/problemLevelBasic/',
      },
    });

    expect(wrapper.find('tbody a.badge').attributes('href')).toBe(
      '/problem/collection/problemLevelBasic/?tag[]=problemTagMatrices&excluded_tag[]=problemTagLoops',
    );
  });
});
