import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';
import { types } from '../../api_types';

import coderOfTheMonth_CandidatesList from './CandidatesList.vue';

const CandidatesList = coderOfTheMonth_CandidatesList as ComponentOptions<Vue>;

const sampleCoder: types.CoderOfTheMonthList = {
  classname: 'user-rank-unranked',
  country_id: 'MX',
  date: '2024-01-01',
  gravatar_32: 'https://example.com/avatar.png',
  problems_solved: 10,
  score: 100,
  username: 'test_user',
};

describe('CandidatesList.vue', () => {
  it('Should render candidate rows', () => {
    const wrapper = shallowMount(CandidatesList, {
      propsData: {
        coders: [sampleCoder],
        isMentor: false,
        selectedTab: 'candidatesToCoderOfTheMonth',
        isDisabled: false,
      },
    });

    expect(wrapper.find('table').exists()).toBeTruthy();
    expect(wrapper.text()).toContain(T.codersOfTheMonthUser);
    expect(wrapper.text()).toContain('10');
    expect(wrapper.text()).toContain('100');
    expect(wrapper.find('img').attributes().alt).toBe(sampleCoder.username);
  });

  it('Should show actions column for mentors', () => {
    const wrapper = shallowMount(CandidatesList, {
      propsData: {
        coders: [sampleCoder],
        isMentor: true,
        selectedTab: 'candidatesToCoderOfTheMonth',
        isDisabled: false,
      },
    });

    expect(wrapper.text()).toContain(T.wordsActions);
  });
});
