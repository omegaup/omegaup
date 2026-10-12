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

  it('Should render tabs without legacy data-toggle attributes', () => {
    const wrapper = shallowMount(schoolOfTheMonth_List, {
      propsData: {
        schoolsOfPreviousMonth: [],
        schoolsOfPreviousMonths: [],
        candidatesToSchoolOfTheMonth: [],
      },
    });

    const tabs = wrapper.findAll('a.nav-link[role="tab"]');
    expect(tabs.length).toBe(3);
    tabs.wrappers.forEach((tab) => {
      expect(tab.attributes('data-toggle')).toBeUndefined();
      expect(tab.attributes('data-bs-toggle')).toBeUndefined();
    });
  });

  it('Should switch active tab and update aria-selected on click', async () => {
    const wrapper = shallowMount(schoolOfTheMonth_List, {
      propsData: {
        schoolsOfPreviousMonth: [],
        schoolsOfPreviousMonths: [],
        candidatesToSchoolOfTheMonth: [],
      },
    });

    const allSchoolsTab = wrapper.find(
      'a.nav-link[aria-controls="allSchoolsOfTheMonth"]',
    );
    const prevMonthTab = wrapper.find(
      'a.nav-link[aria-controls="schoolsOfPreviousMonth"]',
    );
    const candidatesTab = wrapper.find(
      'a.nav-link[aria-controls="candidatesToSchoolOfTheMonth"]',
    );

    // Initial state: allSchoolsOfTheMonth is active
    expect(allSchoolsTab.classes()).toContain('active');
    expect(allSchoolsTab.attributes('aria-selected')).toBe('true');
    expect(prevMonthTab.classes()).not.toContain('active');
    expect(prevMonthTab.attributes('aria-selected')).toBeUndefined();
    expect(candidatesTab.classes()).not.toContain('active');
    expect(candidatesTab.attributes('aria-selected')).toBeUndefined();

    // Switch to schoolsOfPreviousMonth
    await prevMonthTab.trigger('click');
    expect(prevMonthTab.classes()).toContain('active');
    expect(prevMonthTab.attributes('aria-selected')).toBe('true');
    expect(allSchoolsTab.classes()).not.toContain('active');
    expect(allSchoolsTab.attributes('aria-selected')).toBeUndefined();
    expect(candidatesTab.classes()).not.toContain('active');
    expect(candidatesTab.attributes('aria-selected')).toBeUndefined();

    // Switch to candidatesToSchoolOfTheMonth
    await candidatesTab.trigger('click');
    expect(candidatesTab.classes()).toContain('active');
    expect(candidatesTab.attributes('aria-selected')).toBe('true');
    expect(allSchoolsTab.classes()).not.toContain('active');
    expect(allSchoolsTab.attributes('aria-selected')).toBeUndefined();
    expect(prevMonthTab.classes()).not.toContain('active');
    expect(prevMonthTab.attributes('aria-selected')).toBeUndefined();

    // Switch back to allSchoolsOfTheMonth
    await allSchoolsTab.trigger('click');
    expect(allSchoolsTab.classes()).toContain('active');
    expect(allSchoolsTab.attributes('aria-selected')).toBe('true');
    expect(prevMonthTab.classes()).not.toContain('active');
    expect(prevMonthTab.attributes('aria-selected')).toBeUndefined();
    expect(candidatesTab.classes()).not.toContain('active');
    expect(candidatesTab.attributes('aria-selected')).toBeUndefined();
  });
});
