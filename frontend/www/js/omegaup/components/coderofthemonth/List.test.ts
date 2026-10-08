import { shallowMount, mount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';

import coderOfTheMonth_List from './List.vue';
import coderofthemonth_CodersList from './CodersList.vue';
import coderofthemonth_TopCodersList from './TopCodersList.vue';
import coderofthemonth_CandidatesList from './CandidatesList.vue';

const coderofthemonth_List = coderOfTheMonth_List as ComponentOptions<Vue>;

describe('List.vue', () => {
  const codersOfCurrentMonth: types.CoderOfTheMonthList[] = [
    {
      classname: 'user-rank-master',
      country_id: 'MX',
      date: '2026-01',
      gravatar_32: 'https://example.com/avatar1.png',
      username: 'coder_january',
    },
  ];
  const codersOfPreviousMonth: types.CoderOfTheMonthList[] = [
    {
      classname: 'user-rank-expert',
      country_id: 'US',
      date: '2025-12',
      gravatar_32: 'https://example.com/avatar2.png',
      username: 'coder_december',
    },
  ];
  const candidatesToCoderOfTheMonth: types.CoderOfTheMonthList[] = [
    {
      classname: 'user-rank-specialist',
      country_id: 'CA',
      date: '2026-02',
      gravatar_32: 'https://example.com/avatar3.png',
      problems_solved: 42,
      score: 100,
      username: 'candidate_coder',
    },
  ];

  const defaultProps = {
    codersOfCurrentMonth,
    codersOfPreviousMonth,
    candidatesToCoderOfTheMonth,
    isMentor: false,
    canChooseCoder: false,
    coderIsSelected: false,
    category: 'all',
    selectedTab: 'codersOfTheMonth',
  };

  it('Should render initial tab with active tab class and corresponding coders list', () => {
    const wrapper = shallowMount(coderofthemonth_List, {
      propsData: defaultProps,
    });

    expect(wrapper.vm.currentSelectedTab).toBe('codersOfTheMonth');

    const currentTabLink = wrapper.find(
      'a.nav-link[aria-controls="codersOfTheMonth"]',
    );
    expect(currentTabLink.classes()).toContain('active');
    expect(currentTabLink.attributes('aria-selected')).toBe('true');

    const prevTabLink = wrapper.find(
      'a.nav-link[aria-controls="codersOfPreviousMonth"]',
    );
    expect(prevTabLink.classes()).not.toContain('active');

    const candidatesTabLink = wrapper.find(
      'a.nav-link[aria-controls="candidatesToCoderOfTheMonth"]',
    );
    expect(candidatesTabLink.classes()).not.toContain('active');

    // shallowMount stubs child components; check visibleCoders computed property
    // which is the data source passed down to the rendered sub-component.
    expect(wrapper.vm.visibleCoders).toEqual(codersOfCurrentMonth);

    const renderedComponent = wrapper.findComponent(coderofthemonth_CodersList);
    expect(renderedComponent.exists()).toBe(true);
  });

  it('Should update currentSelectedTab, active tab class, and rendered list when selectedTab prop changes to codersOfPreviousMonth', async () => {
    const wrapper = shallowMount(coderofthemonth_List, {
      propsData: defaultProps,
    });

    await wrapper.setProps({ selectedTab: 'codersOfPreviousMonth' });

    expect(wrapper.vm.currentSelectedTab).toBe('codersOfPreviousMonth');

    const prevTabLink = wrapper.find(
      'a.nav-link[aria-controls="codersOfPreviousMonth"]',
    );
    expect(prevTabLink.classes()).toContain('active');
    expect(prevTabLink.attributes('aria-selected')).toBe('true');

    const currentTabLink = wrapper.find(
      'a.nav-link[aria-controls="codersOfTheMonth"]',
    );
    expect(currentTabLink.classes()).not.toContain('active');

    // shallowMount stubs child components; check visibleCoders computed property.
    expect(wrapper.vm.visibleCoders).toEqual(codersOfPreviousMonth);

    const renderedComponent = wrapper.findComponent(
      coderofthemonth_TopCodersList,
    );
    expect(renderedComponent.exists()).toBe(true);
  });

  it('Should update currentSelectedTab, active tab class, and rendered list when selectedTab prop changes to candidatesToCoderOfTheMonth', async () => {
    const wrapper = shallowMount(coderofthemonth_List, {
      propsData: defaultProps,
    });

    await wrapper.setProps({ selectedTab: 'candidatesToCoderOfTheMonth' });

    expect(wrapper.vm.currentSelectedTab).toBe('candidatesToCoderOfTheMonth');

    const candidatesTabLink = wrapper.find(
      'a.nav-link[aria-controls="candidatesToCoderOfTheMonth"]',
    );
    expect(candidatesTabLink.classes()).toContain('active');
    expect(candidatesTabLink.attributes('aria-selected')).toBe('true');

    const currentTabLink = wrapper.find(
      'a.nav-link[aria-controls="codersOfTheMonth"]',
    );
    expect(currentTabLink.classes()).not.toContain('active');

    // shallowMount stubs child components; check visibleCoders computed property.
    expect(wrapper.vm.visibleCoders).toEqual(candidatesToCoderOfTheMonth);

    const renderedComponent = wrapper.findComponent(
      coderofthemonth_CandidatesList,
    );
    expect(renderedComponent.exists()).toBe(true);
  });

  it('Should update currentSelectedTab and window.location.hash when tab is clicked', async () => {
    const wrapper = shallowMount(coderofthemonth_List, {
      propsData: defaultProps,
    });

    const prevTabLink = wrapper.find(
      'a.nav-link[aria-controls="codersOfPreviousMonth"]',
    );
    await prevTabLink.trigger('click');

    expect(wrapper.vm.currentSelectedTab).toBe('codersOfPreviousMonth');
    expect(window.location.hash).toBe('#codersOfPreviousMonth');
  });

  it('Should render female category tab titles correctly', () => {
    const wrapper = shallowMount(coderofthemonth_List, {
      propsData: {
        ...defaultProps,
        category: 'female',
      },
    });

    expect(
      wrapper.find('a.nav-link[aria-controls="codersOfTheMonth"]').text(),
    ).toContain(T.codersOfTheMonthFemale);
    expect(
      wrapper.find('a.nav-link[aria-controls="codersOfPreviousMonth"]').text(),
    ).toContain(T.codersOfTheMonthFemaleRank);
    expect(
      wrapper
        .find('a.nav-link[aria-controls="candidatesToCoderOfTheMonth"]')
        .text(),
    ).toContain(T.codersOfTheMonthFemaleListCandidate);
  });
});

describe('CoderOfTheMonth navigation with hashchange (Back/Forward buttons)', () => {
  const codersOfCurrentMonth: types.CoderOfTheMonthList[] = [
    {
      classname: 'user-rank-master',
      country_id: 'MX',
      date: '2026-01',
      gravatar_32: 'https://example.com/avatar1.png',
      username: 'coder_january',
    },
  ];
  const codersOfPreviousMonth: types.CoderOfTheMonthList[] = [
    {
      classname: 'user-rank-expert',
      country_id: 'US',
      date: '2025-12',
      gravatar_32: 'https://example.com/avatar2.png',
      username: 'coder_december',
    },
  ];
  const candidatesToCoderOfTheMonth: types.CoderOfTheMonthList[] = [
    {
      classname: 'user-rank-specialist',
      country_id: 'CA',
      date: '2026-02',
      gravatar_32: 'https://example.com/avatar3.png',
      problems_solved: 42,
      score: 100,
      username: 'candidate_coder',
    },
  ];

  const defaultProps = {
    codersOfCurrentMonth,
    codersOfPreviousMonth,
    candidatesToCoderOfTheMonth,
    isMentor: false,
    canChooseCoder: false,
    coderIsSelected: false,
    category: 'all',
  };

  function getSelectedValidTab(tab: string): string {
    const validTabs = [
      'codersOfTheMonth',
      'codersOfPreviousMonth',
      'candidatesToCoderOfTheMonth',
    ];
    return validTabs.includes(tab) ? tab : 'codersOfTheMonth';
  }

  it('Should update child component tab when browser back/forward dispatches hashchange event', async () => {
    window.location.hash = '#codersOfTheMonth';

    const ParentComponent = Vue.extend({
      components: {
        'omegaup-coder-of-the-month-list': coderofthemonth_List,
      },
      data: () => ({
        selectedTab: getSelectedValidTab(
          window.location.hash.substring(1).split('/')[0],
        ),
      }),
      render(createElement) {
        return createElement('omegaup-coder-of-the-month-list', {
          props: {
            ...defaultProps,
            selectedTab: this.selectedTab,
          },
        });
      },
    });

    const wrapper = mount(ParentComponent);

    const onHashChange = () => {
      const hash = window.location.hash.substring(1).split('/')[0];
      wrapper.vm.selectedTab = getSelectedValidTab(hash);
    };

    window.addEventListener('hashchange', onHashChange);
    wrapper.vm.$once('hook:beforeDestroy', () => {
      window.removeEventListener('hashchange', onHashChange);
    });

    const childList = wrapper.findComponent(coderofthemonth_List);
    const childVm = childList.vm as any;
    expect(childVm.currentSelectedTab).toBe('codersOfTheMonth');
    expect(
      childList.find('a.nav-link[aria-controls="codersOfTheMonth"]').classes(),
    ).toContain('active');
    expect(childList.findComponent(coderofthemonth_CodersList).exists()).toBe(
      true,
    );

    // Simulate navigation to codersOfPreviousMonth
    window.location.hash = '#codersOfPreviousMonth';
    window.dispatchEvent(new Event('hashchange'));
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.selectedTab).toBe('codersOfPreviousMonth');
    expect(childVm.currentSelectedTab).toBe('codersOfPreviousMonth');
    expect(
      childList
        .find('a.nav-link[aria-controls="codersOfPreviousMonth"]')
        .classes(),
    ).toContain('active');
    expect(
      childList.find('a.nav-link[aria-controls="codersOfTheMonth"]').classes(),
    ).not.toContain('active');
    expect(
      childList.findComponent(coderofthemonth_TopCodersList).exists(),
    ).toBe(true);

    // Simulate navigation to candidatesToCoderOfTheMonth
    window.location.hash = '#candidatesToCoderOfTheMonth';
    window.dispatchEvent(new Event('hashchange'));
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.selectedTab).toBe('candidatesToCoderOfTheMonth');
    expect(childVm.currentSelectedTab).toBe('candidatesToCoderOfTheMonth');
    expect(
      childList
        .find('a.nav-link[aria-controls="candidatesToCoderOfTheMonth"]')
        .classes(),
    ).toContain('active');
    expect(
      childList.findComponent(coderofthemonth_CandidatesList).exists(),
    ).toBe(true);

    // Simulate Browser Back Button navigation: returns to codersOfPreviousMonth
    window.location.hash = '#codersOfPreviousMonth';
    window.dispatchEvent(new Event('hashchange'));
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.selectedTab).toBe('codersOfPreviousMonth');
    expect(childVm.currentSelectedTab).toBe('codersOfPreviousMonth');
    expect(
      childList
        .find('a.nav-link[aria-controls="codersOfPreviousMonth"]')
        .classes(),
    ).toContain('active');
    expect(
      childList.findComponent(coderofthemonth_TopCodersList).exists(),
    ).toBe(true);

    // Simulate Browser Back Button navigation again: returns to initial tab
    window.location.hash = '#codersOfTheMonth';
    window.dispatchEvent(new Event('hashchange'));
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.selectedTab).toBe('codersOfTheMonth');
    expect(childVm.currentSelectedTab).toBe('codersOfTheMonth');
    expect(
      childList.find('a.nav-link[aria-controls="codersOfTheMonth"]').classes(),
    ).toContain('active');
    expect(childList.findComponent(coderofthemonth_CodersList).exists()).toBe(
      true,
    );

    // Clean up
    wrapper.destroy();
  });
});
