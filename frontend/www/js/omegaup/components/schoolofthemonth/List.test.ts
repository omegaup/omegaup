import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import { omegaup } from '../../omegaup';
import T from '../../lang';

import schoolOfTheMonth_List from './List.vue';

const List = schoolOfTheMonth_List as ComponentOptions<Vue>;

const sampleSchool1: omegaup.SchoolOfTheMonth = {
  school_id: 1,
  name: 'School Alpha',
  country_id: 'MX',
  score: 100,
  time: '2024-01',
};

const sampleSchool2: omegaup.SchoolOfTheMonth = {
  school_id: 2,
  name: 'School Beta',
  country_id: 'US',
  score: 80,
  time: '2024-02',
};

const sampleSchoolCandidate: omegaup.SchoolOfTheMonth = {
  school_id: 3,
  name: 'School Gamma',
  country_id: 'BR',
  score: 60,
};

describe('List.vue', () => {
  it('Should use default props when none provided', () => {
    const wrapper = shallowMount(List, {
      propsData: {},
    });

    expect(wrapper.find('.system-in-maintainance').exists()).toBe(true);
    expect(wrapper.find('table').exists()).toBe(false);
  });

  it('Should handle empty lists', () => {
    const wrapper = shallowMount(List, {
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

  it('Should show maintenance message when isDisabled is true', () => {
    const wrapper = shallowMount(List, {
      propsData: {
        isDisabled: true,
      },
    });

    expect(wrapper.find('.system-in-maintainance').exists()).toBe(true);
    expect(wrapper.find('table').exists()).toBe(false);
  });

  it('Should render school list when isDisabled is false', () => {
    const wrapper = shallowMount(List, {
      propsData: {
        isDisabled: false,
        schoolsOfPreviousMonths: [sampleSchool1],
      },
    });

    expect(wrapper.find('.system-in-maintainance').exists()).toBe(false);
    expect(wrapper.find('table').exists()).toBe(true);
    expect(wrapper.text()).toContain(sampleSchool1.name);
    expect(
      wrapper
        .find(`a[href="/schools/profile/${sampleSchool1.school_id}/"]`)
        .exists(),
    ).toBe(true);
  });

  it('Should switch visible schools and headers on tab click', async () => {
    const wrapper = shallowMount(List, {
      propsData: {
        isDisabled: false,
        schoolsOfPreviousMonths: [sampleSchool1],
        schoolsOfPreviousMonth: [sampleSchool2],
        candidatesToSchoolOfTheMonth: [sampleSchoolCandidate],
      },
    });

    // Default tab: allSchoolsOfTheMonth
    expect(wrapper.text()).toContain(sampleSchool1.name);
    expect(wrapper.text()).not.toContain(sampleSchool2.name);
    expect(wrapper.text()).toContain(T.wordsDate);

    // Click previous month tab
    await wrapper
      .find('a.nav-link[aria-controls="schoolsOfPreviousMonth"]')
      .trigger('click');

    expect(wrapper.text()).toContain(sampleSchool2.name);
    expect(wrapper.text()).not.toContain(sampleSchool1.name);

    // Click candidates tab
    await wrapper
      .find('a.nav-link[aria-controls="candidatesToSchoolOfTheMonth"]')
      .trigger('click');

    expect(wrapper.text()).toContain(sampleSchoolCandidate.name);
    expect(wrapper.text()).not.toContain(sampleSchool2.name);
    expect(wrapper.text()).toContain(T.rankScore);

    // Switch back to allSchoolsOfTheMonth tab
    await wrapper
      .find('a.nav-link[aria-controls="allSchoolsOfTheMonth"]')
      .trigger('click');

    expect(wrapper.text()).toContain(sampleSchool1.name);
    expect(wrapper.text()).toContain(T.wordsDate);
  });

  it('Should handle mentor actions and emit select-school event', async () => {
    const wrapper = shallowMount(List, {
      propsData: {
        isDisabled: false,
        candidatesToSchoolOfTheMonth: [sampleSchoolCandidate],
        isMentor: true,
        canChooseSchool: true,
        schoolIsSelected: false,
      },
    });

    // Switch to candidates tab
    await wrapper
      .find('a.nav-link[aria-controls="candidatesToSchoolOfTheMonth"]')
      .trigger('click');

    expect(wrapper.text()).toContain(T.wordsActions);
    const selectButton = wrapper.find('button.btn-primary');
    expect(selectButton.exists()).toBe(true);
    expect(selectButton.text()).toContain(T.schoolOfTheMonthChooseAsSchool);

    // Click button to select school
    await selectButton.trigger('click');
    expect(wrapper.emitted('select-school')).toBeTruthy();
    expect(wrapper.emitted('select-school')?.[0]).toEqual([
      sampleSchoolCandidate.school_id,
    ]);
  });

  it('Should not show choose button when cannot choose or school already selected', async () => {
    // Case 1: canChooseSchool is false
    const wrapper1 = shallowMount(List, {
      propsData: {
        isDisabled: false,
        candidatesToSchoolOfTheMonth: [sampleSchoolCandidate],
        isMentor: true,
        canChooseSchool: false,
        schoolIsSelected: false,
      },
    });

    await wrapper1
      .find('a.nav-link[aria-controls="candidatesToSchoolOfTheMonth"]')
      .trigger('click');

    expect(wrapper1.text()).toContain(T.wordsActions);
    expect(wrapper1.find('button.btn-primary').exists()).toBe(false);

    // Case 2: schoolIsSelected is true
    const wrapper2 = shallowMount(List, {
      propsData: {
        isDisabled: false,
        candidatesToSchoolOfTheMonth: [sampleSchoolCandidate],
        isMentor: true,
        canChooseSchool: true,
        schoolIsSelected: true,
      },
    });

    await wrapper2
      .find('a.nav-link[aria-controls="candidatesToSchoolOfTheMonth"]')
      .trigger('click');

    expect(wrapper2.text()).toContain(T.wordsActions);
    expect(wrapper2.find('button.btn-primary').exists()).toBe(false);
  });

  it('Should not show actions column when user is not a mentor', async () => {
    const wrapper = shallowMount(List, {
      propsData: {
        isDisabled: false,
        candidatesToSchoolOfTheMonth: [sampleSchoolCandidate],
        isMentor: false,
      },
    });

    // Switch to candidates tab
    await wrapper
      .find('a.nav-link[aria-controls="candidatesToSchoolOfTheMonth"]')
      .trigger('click');

    expect(wrapper.text()).not.toContain(T.wordsActions);
    expect(wrapper.find('button.btn-primary').exists()).toBe(false);
  });
});
