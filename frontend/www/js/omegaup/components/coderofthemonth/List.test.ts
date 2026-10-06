import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';
import { types } from '../../api_types';

import coderOfTheMonth_List from './List.vue';

const List = coderOfTheMonth_List as ComponentOptions<Vue>;

const sampleCoder: types.CoderOfTheMonthList = {
  classname: 'user-rank-unranked',
  country_id: 'MX',
  date: '2024-01-01',
  gravatar_32: 'https://example.com/avatar.png',
  problems_solved: 10,
  score: 100,
  username: 'test_user',
};

describe('List.vue', () => {
  const propsData = {
    codersOfCurrentMonth: [sampleCoder],
    codersOfPreviousMonth: [],
    candidatesToCoderOfTheMonth: [],
    canChooseCoder: false,
    coderIsSelected: false,
    isMentor: false,
    category: 'all',
    selectedTab: 'codersOfTheMonth',
  };

  it('Should render tabs for all category', () => {
    const wrapper = shallowMount(List, {
      propsData,
    });

    expect(
      wrapper.find('a.nav-link[aria-controls="codersOfTheMonth"]').text(),
    ).toContain(T.codersOfTheMonth);
    expect(
      wrapper.find('a.nav-link[aria-controls="codersOfPreviousMonth"]').text(),
    ).toContain(T.codersOfTheMonthRank);
    expect(
      wrapper
        .find('a.nav-link[aria-controls="candidatesToCoderOfTheMonth"]')
        .text(),
    ).toContain(T.codersOfTheMonthListCandidate);
  });

  it('Should render female category tab titles', () => {
    const wrapper = shallowMount(List, {
      propsData: {
        ...propsData,
        category: 'female',
      },
    });

    expect(
      wrapper.find('a.nav-link[aria-controls="codersOfTheMonth"]').text(),
    ).toContain(T.codersOfTheMonthFemale);
  });
});
