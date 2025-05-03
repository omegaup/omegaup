import { shallowMount } from '@vue/test-utils';
import T from '../../lang';
import qualitynomination_ReviewerPopup from './ReviewerPopup.vue';

describe('ReviewerPopup.vue', () => {
  it('Should handle form', () => {
    const wrapper = shallowMount(qualitynomination_ReviewerPopup, {
      propsData: {
        allowUserAddTags: true,
        levelTags: [],
        problemLevel: 'problemLevelBasicIntroductionToProgramming',
        possibleTags: [
          'problemLevelAdvancedCompetitiveProgramming',
          'problemLevelAdvancedSpecializedTopics',
          'problemLevelBasicIntroductionToProgramming',
          'problemLevelBasicKarel',
          'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
          'problemLevelIntermediateDataStructuresAndAlgorithms',
          'problemLevelIntermediateMathsInProgramming',
        ],
        publicTags: [],
        reviewedProblemLevel: null,
        reviewedQualitySeal: false,
        reviewedPublicTags: [],
        selectedPublicTags: [],
        selectedPrivateTags: [],
        problemAlias: 'example-problem',
        problemTitle: 'Example Problem',
      },
    });

    expect(wrapper.text()).toContain(T.reviewerNominationFormTitle);
  });
});
