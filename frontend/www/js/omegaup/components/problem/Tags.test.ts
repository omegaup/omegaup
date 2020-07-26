import { mount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import { types } from '../../api_types';
import T from '../../lang';
import { omegaup } from '../../omegaup';

import problem_Tags from './Tags.vue';

const problemTagsPropsData = {
  alias: '',
  canAddNewTags: true,
  initialAllowTags: true,
  isCreate: true,
  levelTags: <string[]>[
    'problemLevelAdvancedCompetitiveProgramming',
    'problemLevelAdvancedSpecializedTopics',
    'problemLevelBasicIntroductionToProgramming',
    'problemLevelBasicKarel',
    'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
    'problemLevelIntermediateDataStructuresAndAlgorithms',
    'problemLevelIntermediateMathsInProgramming',
  ],
  problemLevel: null,
  publicTags: <string[]>['some', 'public', 'tags'],
  selectedPrivateTags: <string[]>[],
  selectedPublicTags: <string[]>[],
  title: '',
};

describe('Tags.vue', () => {
  it('Should handle problem tags form when problem is created', () => {
    const wrapper = mount(problem_Tags, {
      propsData: problemTagsPropsData,
    });

    expect(wrapper.text()).not.toContain(T.updateProblemLevel);
  });

  it('Should handle problem tags form when problem is edited', () => {
    const wrapper = mount(problem_Tags, {
      propsData: problemTagsPropsData,
    });

    expect(wrapper.text()).not.toContain(T.updateProblemLevel);
  });
});
