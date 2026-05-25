import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';

import T from '../../lang';

import problem_Mine from './Mine.vue';

describe('Mine.vue', () => {
  const defaultProps = {
    isSysadmin: false,
    problems: [],
    pagerItems: [
      {
        class: 'disabled',
        label: '1',
        page: 1,
      },
    ],
    privateProblemsAlert: false,
    visibilityStatuses: {
      deleted: -10,
      private: 0,
      privateBanned: -2,
      privateWarning: -1,
      public: 1,
      publicBanned: -4,
      publicWarning: -3,
    },
    query: null,
    isSearching: false,
  };

  it('Should handle empty list of problems', async () => {
    const wrapper = shallowMount(problem_Mine, {
      propsData: defaultProps,
    });

    expect(wrapper.text()).toContain(T.myproblemsListMyProblems);
    expect(wrapper.text()).toContain(T.courseAssignmentProblemsEmpty);
  });

  it('Should emit search after debounce while typing', async () => {
    jest.useFakeTimers();
    const wrapper = shallowMount(problem_Mine, {
      propsData: defaultProps,
    });

    const input = wrapper.find('input.typeahead');
    await input.setValue('Mov');

    jest.advanceTimersByTime(299);
    await Vue.nextTick();
    expect(wrapper.emitted('search-problems')).toBeUndefined();

    jest.advanceTimersByTime(1);
    await Vue.nextTick();
    expect(wrapper.emitted('search-problems')).toEqual([['Mov']]);

    jest.useRealTimers();
  });

  it('Should emit search immediately on enter', async () => {
    jest.useFakeTimers();
    const wrapper = shallowMount(problem_Mine, {
      propsData: defaultProps,
    });

    const input = wrapper.find('input.typeahead');
    await input.setValue('Movie');
    await input.trigger('keyup.enter');

    expect(wrapper.emitted('search-problems')).toEqual([['Movie']]);

    jest.runOnlyPendingTimers();
    jest.useRealTimers();
  });

  it('Should show no results found for an empty search result', async () => {
    const wrapper = shallowMount(problem_Mine, {
      propsData: {
        ...defaultProps,
        query: 'missing-problem',
      },
    });

    expect(wrapper.text()).toContain(T.wordsNoResultsFound);
  });
});
