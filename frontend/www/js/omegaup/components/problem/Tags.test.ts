import { mount } from '@vue/test-utils';

import T from '../../lang';

import problem_Tags from './Tags.vue';

const baseProblemTagsPropsData = {
  alias: '',
  canAddNewTags: true,
  initialAllowTags: true,
  isCreate: true,
  levelTags: [
    'problemLevelAdvancedCompetitiveProgramming',
    'problemLevelAdvancedSpecializedTopics',
    'problemLevelBasicIntroductionToProgramming',
    'problemLevelBasicKarel',
    'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
    'problemLevelIntermediateDataStructuresAndAlgorithms',
    'problemLevelIntermediateMathsInProgramming',
  ],
  publicTags: ['some', 'public', 'tags'],
  selectedPrivateTags: [] as string[],
  selectedPublicTags: [] as string[],
  title: '',
};

describe('Tags.vue', () => {
  it('Should handle problem tags form when problem is created', () => {
    const wrapper = mount(problem_Tags, {
      propsData: baseProblemTagsPropsData,
    });

    expect(wrapper.text()).not.toContain(T.updateProblemLevel);
  });

  it('Should handle problem tags form when problem is edited', () => {
    const wrapper = mount(problem_Tags, {
      propsData: Object.assign({}, baseProblemTagsPropsData, {
        isCreate: false,
      }),
    });

    expect(wrapper.text()).toContain(T.updateProblemLevel);
  });
});
