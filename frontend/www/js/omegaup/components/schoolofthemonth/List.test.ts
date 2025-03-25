import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import schoolOfTheMonth_List from './List.vue';

describe('List.vue', () => {
  it('Should handle empty lists', () => {
    const wrapper = shallowMount(schoolOfTheMonth_List, {
      propsData: {
        schoolsOfPreviousMonth: [],
        schoolsOfPreviousMonths: [],
        candidatesToSchoolOfTheMonth: [],
        isMentor: true,
        canChooseSchool: true,
        schoolIsSelected: true,
      },
    });

    expect(
      wrapper.find('a.nav-link[aria-controls="allSchoolsOfTheMonth"]').text(),
    ).toContain(T.schoolsOfTheMonth);
    expect(
      wrapper.find('a.nav-link[aria-controls="schoolsOfPreviousMonth"]').text(),
    ).toContain(T.schoolsOfTheMonthRank);
    expect(
      wrapper
        .find('a.nav-link[aria-controls="candidatesToSchoolOfTheMonth"]')
        .text(),
    ).toContain(T.schoolsOfTheMonthCandidates);
  });
});
