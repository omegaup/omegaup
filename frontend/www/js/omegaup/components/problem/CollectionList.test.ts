import { mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import problem_CollectionList from './CollectionList.vue';

describe('CollectionList.vue', () => {
  it('Should handle details of problem list collection by level', async () => {
    const wrapper = mount(problem_CollectionList, {
      propsData: {
        data: {
          level: 'problemLevelBasicIntroductionToProgramming',
          frequentTags: [
            { alias: 'problemTagMatrices' },
            { alias: 'problemTagDiophantineEquations' },
            { alias: 'problemTagInputAndOutput' },
            { alias: 'problemTagArrays' },
          ],
          publicTags: <string[]>['problemTagConditionals', 'problemTagLoops'],
        },
        difficulty: 'easy',
        selectedTags: <string[]>[
          'problemTagMatrices',
          'problemTagDiophantineEquations',
        ],
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

    const checkboxInput1 = <HTMLInputElement>(
      wrapper.find('input[value="problemTagMatrices"]').element
    );
    const checkboxInput2 = <HTMLInputElement>(
      wrapper.find('input[value="problemTagDiophantineEquations"]').element
    );
    const checkboxInput3 = <HTMLInputElement>(
      wrapper.find('input[value="problemTagInputAndOutput"]').element
    );
    const checkboxInput4 = <HTMLInputElement>(
      wrapper.find('input[value="problemTagArrays"]').element
    );
    const radioInput1 = <HTMLInputElement>(
      wrapper.find('input[value="all"]').element
    );
    const radioInput2 = <HTMLInputElement>(
      wrapper.find('input[value="easy"]').element
    );

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

  it('Should handle empty list of problems in collection by level', async () => {
    const wrapper = mount(problem_CollectionList, {
      propsData: {
        data: {
          level: 'problemLevelBasicIntroductionToProgramming',
          frequentTags: [{ alias: 'problemTagMatrices' }],
          publicTags: <string[]>['problemTagConditionals'],
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
