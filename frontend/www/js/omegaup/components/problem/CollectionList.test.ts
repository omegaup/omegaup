import { mount } from '@vue/test-utils';

import T from '../../lang';
import type { types } from '../../api_types';

import problem_CollectionList from './CollectionList.vue';
import problem_FilterTags from './FilterTags.vue';
import problem_FilterDifficulty from './FilterDifficulty.vue';
import problem_FilterQuality from './FilterQuality.vue';
import problem_BaseList from './BaseList.vue';

describe('CollectionList.vue', () => {
  it('Should handle details of problem list collection by level', async () => {
    const wrapper = mount(problem_CollectionList, {
      propsData: {
        data: {
          level: 'problemLevelBasicIntroductionToProgramming',
          frequentTags: [
            { name: 'problemTagMatrices', problemCount: 1 },
            { name: 'problemTagDiophantineEquations', problemCount: 1 },
            { name: 'problemTagInputAndOutput', problemCount: 1 },
            { name: 'problemTagArrays', problemCount: 1 },
          ] as types.TagWithProblemCount[],
          publicTags: ['problemTagConditionals', 'problemTagLoops'],
        },
        difficulty: 'easy',
        selectedTags: ['problemTagMatrices', 'problemTagDiophantineEquations'],
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

    expect(wrapper.text()).toContain(
      T.problemLevelBasicIntroductionToProgramming,
    );
    expect(wrapper.text()).toContain(T.problemEditAddTags);
    expect(wrapper.text()).toContain(T.wordsDifficulty);
    expect(wrapper.text()).toContain('Problem 1');
    expect(wrapper.text()).not.toContain(T.courseAssignmentProblemsEmpty);

    expect(wrapper.find('input[value="problemTagMatrices"]').exists()).toBe(
      true,
    );
    expect(
      wrapper.find('input[value="problemTagDiophantineEquations"]').exists(),
    ).toBe(true);
    expect(
      wrapper.find('input[value="problemTagInputAndOutput"]').exists(),
    ).toBe(true);
    expect(wrapper.find('input[value="problemTagArrays"]').exists()).toBe(true);

    expect(wrapper.find('input[value="problemTagConditionals"]').exists()).toBe(
      false,
    );
    expect(wrapper.find('input[value="problemTagLoops"]').exists()).toBe(false);

    const checkboxInput1 = wrapper.find('input[value="problemTagMatrices"]')
      .element as HTMLInputElement;
    const checkboxInput2 = wrapper.find(
      'input[value="problemTagDiophantineEquations"]',
    ).element as HTMLInputElement;
    const checkboxInput3 = wrapper.find(
      'input[value="problemTagInputAndOutput"]',
    ).element as HTMLInputElement;
    const checkboxInput4 = wrapper.find('input[value="problemTagArrays"]')
      .element as HTMLInputElement;
    const radioInput1 = wrapper.find('input[value="all"]')
      .element as HTMLInputElement;
    const radioInput2 = wrapper.find('input[value="easy"]')
      .element as HTMLInputElement;

    expect(checkboxInput1.checked).toBeTruthy();
    expect(checkboxInput2.checked).toBeTruthy();
    expect(checkboxInput3.checked).toBeFalsy();
    expect(checkboxInput4.checked).toBeFalsy();
    expect(radioInput1.checked).toBeFalsy();
    expect(radioInput2.checked).toBeTruthy();

    checkboxInput1.click();
    checkboxInput2.click();
    checkboxInput3.click();
    checkboxInput4.click();
    radioInput1.click();

    expect(checkboxInput1.checked).toBeFalsy();
    expect(checkboxInput2.checked).toBeFalsy();
    expect(checkboxInput3.checked).toBeTruthy();
    expect(checkboxInput4.checked).toBeTruthy();
    expect(radioInput1.checked).toBeTruthy();
    expect(radioInput2.checked).toBeFalsy();
  });

  it.each([
    { selectedTags: ['problemTagConditionals'], excludedTags: [], index: 0 },
    { selectedTags: [], excludedTags: ['problemTagConditionals'], index: 1 },
  ])(
    'Should keep selected public quality tags visible and restore their state',
    async ({ selectedTags, excludedTags, index }) => {
      const tagName = 'problemTagConditionals';
      const wrapper = mount(problem_CollectionList, {
        propsData: {
          data: {
            level: 'problemLevelBasicIntroductionToProgramming',
            frequentTags: [],
            publicTags: [{ name: tagName, problemCount: 2 }],
          },
          selectedTags,
          excludedTags,
        },
      });

      const checkboxes = wrapper.findAll(
        `.tag-filter-row input[type="checkbox"][value="${tagName}"]`,
      );
      expect(checkboxes).toHaveLength(2);
      expect((checkboxes.at(index).element as HTMLInputElement).checked).toBe(
        true,
      );
      expect(
        (checkboxes.at(1 - index).element as HTMLInputElement).checked,
      ).toBe(false);

      await wrapper
        .findComponent(problem_FilterTags)
        .vm.$emit('filters-changed', {
          selected: ['problemTagArrays'],
          excluded: [tagName],
        });
      expect(wrapper.emitted('apply-filter')?.[0]).toEqual([
        undefined,
        undefined,
        undefined,
        undefined,
        ['problemTagArrays'],
        [tagName],
      ]);
    },
  );

  it.each([
    {
      source: problem_FilterDifficulty,
      event: 'change-difficulty',
      value: 'hard',
      expectedFilters: [
        undefined,
        undefined,
        'hard',
        'onlyQualityProblems',
        ['problemTagMatrices'],
        ['problemTagConditionals'],
      ],
    },
    {
      source: problem_FilterQuality,
      event: 'change-quality',
      value: 'all',
      expectedFilters: [
        undefined,
        undefined,
        'all',
        'all',
        ['problemTagMatrices'],
        ['problemTagConditionals'],
      ],
    },
    {
      source: problem_BaseList,
      event: 'apply-filter',
      value: ['title', 'asc'],
      expectedFilters: [
        'title',
        'asc',
        'all',
        'onlyQualityProblems',
        ['problemTagMatrices'],
        ['problemTagConditionals'],
      ],
    },
  ])(
    'Should preserve excluded tags when $event changes',
    async ({ source, event, value, expectedFilters }) => {
      const wrapper = mount(problem_CollectionList, {
        propsData: {
          data: {
            level: 'problemLevelBasicIntroductionToProgramming',
            frequentTags: [],
            publicTags: [],
          },
          difficulty: 'all',
          quality: 'onlyQualityProblems',
          selectedTags: ['problemTagMatrices'],
          excludedTags: ['problemTagConditionals'],
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
              tags: [],
            },
          ],
        },
      });

      await wrapper
        .findComponent(source)
        .vm.$emit(event, ...(Array.isArray(value) ? value : [value]));
      expect(wrapper.emitted('apply-filter')?.[0]).toEqual(expectedFilters);
    },
  );

  it('Should toggle problem tags visibility', async () => {
    const wrapper = mount(problem_CollectionList, {
      propsData: {
        data: {
          level: 'problemLevelBasicIntroductionToProgramming',
          frequentTags: [] as types.TagWithProblemCount[],
          publicTags: [],
        },
        problems: [
          {
            alias: 'Problem-1',
            title: 'Problem 1',
            difficulty: 2,
            points: 100,
            problem_id: 1,
            quality: 2,
            ratio: 0,
            accepted: 0,
            submissions: 0,
            visibility: 2,
            tags: [
              {
                name: 'problemTagMatrices',
                source: 'owner',
              },
            ],
          },
        ],
      },
    });

    // Toggle switch should display its label text
    expect(wrapper.find('[data-problem-tags-toggle]').text()).toContain(
      T.userEditShowProblemTags,
    );
    // Toggle is initially checked (tags visible)
    const toggleInput = wrapper.find(
      '[data-problem-tags-toggle] input[type="checkbox"]',
    );
    expect((toggleInput.element as HTMLInputElement).checked).toBe(true);
    expect(wrapper.findAll('tbody a.badge').length).toBe(1);
    // Check that header badges (tag classification indicators) are visible
    expect(
      wrapper.findAll('thead .badge.custom-badge-quality').length,
    ).toBeGreaterThan(0);
    expect(
      wrapper.findAll('thead .badge.custom-badge-owner').length,
    ).toBeGreaterThan(0);
    expect(
      wrapper.findAll('thead .badge.custom-badge-voted').length,
    ).toBeGreaterThan(0);

    // Click the checkbox to toggle off (hide tags)
    await toggleInput.setChecked(false);
    expect((toggleInput.element as HTMLInputElement).checked).toBe(false);
    expect(wrapper.findAll('tbody a.badge').length).toBe(0);
    // Check that header badges (tag classification indicators) are hidden
    expect(wrapper.findAll('thead .badge.custom-badge-quality').length).toBe(0);
    expect(wrapper.findAll('thead .badge.custom-badge-owner').length).toBe(0);
    expect(wrapper.findAll('thead .badge.custom-badge-voted').length).toBe(0);

    // Click the checkbox to toggle on (show tags)
    await toggleInput.setChecked(true);
    expect((toggleInput.element as HTMLInputElement).checked).toBe(true);
    expect(wrapper.findAll('tbody a.badge').length).toBe(1);
    // Check that header badges (tag classification indicators) are visible again
    expect(
      wrapper.findAll('thead .badge.custom-badge-quality').length,
    ).toBeGreaterThan(0);
    expect(
      wrapper.findAll('thead .badge.custom-badge-owner').length,
    ).toBeGreaterThan(0);
    expect(
      wrapper.findAll('thead .badge.custom-badge-voted').length,
    ).toBeGreaterThan(0);
  });

  it('Should handle empty list of problems in collection by level', async () => {
    const wrapper = mount(problem_CollectionList, {
      propsData: {
        data: {
          level: 'problemLevelBasicIntroductionToProgramming',
          frequentTags: [{ alias: 'problemTagMatrices' }],
          publicTags: ['problemTagConditionals'],
        },
        difficulty: 'all',
      },
    });

    expect(wrapper.text()).toContain(
      T.problemLevelBasicIntroductionToProgramming,
    );
    expect(wrapper.text()).toContain(T.problemEditAddTags);
    expect(wrapper.text()).toContain(T.wordsDifficulty);
    expect(wrapper.text()).toContain(T.courseAssignmentProblemsEmpty);
  });
});
