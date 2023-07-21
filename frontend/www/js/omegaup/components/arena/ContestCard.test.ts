jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';
import T from '../../lang';
import arena_ContestCard from './ContestCard.vue';
import { ContestTab } from './ContestListv2.vue';

describe('ContestCard.vue', () => {
  const daySeconds = 24 * 60 * 60 * 1000;
  const today = new Date();
  const yesterday = new Date(today.getTime() - daySeconds);
  const tomorrow = new Date(today.getTime() + daySeconds);

  const currentTab: ContestTab = ContestTab.Current;
  const futureTab: ContestTab = ContestTab.Future;
  const pastTab: ContestTab = ContestTab.Past;

  const currentContest: types.ContestListItem = {
    admission_mode: 'public',
    alias: 'Current-Contest-1',
    description: 'hello contest 1',
    contest_id: 1,
    contestants: 12,
    finish_time: tomorrow,
    last_updated: yesterday,
    organizer: 'omegaup',
    original_finish_time: tomorrow,
    score_mode: 'all_or_nothing',
    participating: true,
    problemset_id: 1,
    recommended: false,
    start_time: yesterday,
    title: 'Current Contest 1',
    window_length: 300,
  };

  const futureContest: types.ContestListItem = {
    admission_mode: 'public',
    alias: 'Future-Contest-1',
    description: 'hello contest 1',
    contest_id: 1,
    contestants: 12,
    finish_time: new Date(tomorrow.getTime() + daySeconds),
    last_updated: today,
    organizer: 'omegaup',
    original_finish_time: new Date(tomorrow.getTime() + daySeconds),
    score_mode: 'all_or_nothing',
    participating: true,
    problemset_id: 1,
    recommended: true,
    start_time: tomorrow,
    title: 'Future Contest 1',
    window_length: 300,
  };

  const pastContest: types.ContestListItem = {
    admission_mode: 'public',
    alias: 'Past-Contest-1',
    description: 'hello contest 1',
    contest_id: 1,
    contestants: 12,
    finish_time: yesterday,
    last_updated: new Date(yesterday.getTime() - daySeconds),
    organizer: 'omegaup',
    original_finish_time: yesterday,
    score_mode: 'all_or_nothing',
    participating: true,
    problemset_id: 1,
    recommended: false,
    start_time: new Date(yesterday.getTime() - daySeconds),
    title: 'Past Contest 1',
    window_length: 300,
  };

  it('Should show the current contest card', async () => {
    const wrapper = mount(arena_ContestCard, {
      propsData: {
        contest: currentContest,
        contestTab: currentTab,
      },
    });

    const contestEnrollStatus = wrapper.findComponent({
      ref: 'contestEnrollStatus',
    });

    const contestButtonEnter = wrapper.findComponent({
      ref: 'contestButtonEnter',
    });

    expect(contestEnrollStatus.exists()).toBe(true);
    expect(contestButtonEnter.text()).toBe(T.contestButtonEnter);
  });

  it('Should show the future contest card', async () => {
    const wrapper = mount(arena_ContestCard, {
      propsData: {
        contest: futureContest,
        contestTab: futureTab,
      },
    });

    const contestEnrollStatus = wrapper.findComponent({
      ref: 'contestEnrollStatus',
    });
    const contestButtonEnter = wrapper.findComponent({
      ref: 'contestButtonEnter',
    });
    const contestButtonSingUp = wrapper.findComponent({
      ref: 'contestButtonSingUp',
    });
    const contestIconRecommended = wrapper.findComponent({
      ref: 'contestIconRecommended',
    });

    expect(contestEnrollStatus.exists()).toBe(true);
    expect(contestButtonEnter.exists()).toBe(true);
    expect(contestIconRecommended.exists()).toBe(true);
    expect(contestButtonSingUp.exists()).toBe(false);
  });

  it('Should show the past contest card', async () => {
    const wrapper = mount(arena_ContestCard, {
      propsData: {
        contest: pastContest,
        contestTab: pastTab,
      },
    });

    const contestButtonScoreboard = wrapper.findComponent({
      ref: 'contestButtonScoreboard',
    });

    expect(contestButtonScoreboard.exists()).toBe(true);
  });
});
