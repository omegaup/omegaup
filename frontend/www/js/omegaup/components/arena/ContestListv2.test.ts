jest.mock('../../../../third_party/js/diff_match_patch.js');

import T from '../../lang';
import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';

import arena_ContestList from './ContestListv2.vue';

describe('ContestListv2.vue', () => {
  const daySeconds = 24 * 60 * 60 * 1000;
  const today = new Date();
  const yesterday = new Date(today.getTime() - daySeconds);
  const tomorrow = new Date(today.getTime() + daySeconds);

  const contests: types.ContestList = {
    current: [
      {
        admission_mode: 'public',
        alias: 'Current-Contest-1',
        description: 'hello contest 1',
        contest_id: 1,
        contestants: 12,
        finish_time: tomorrow,
        last_updated: yesterday,
        organizer: 'omegaup',
        original_finish_time: tomorrow,
        partial_score: false,
        participating: true,
        problemset_id: 1,
        recommended: false,
        start_time: yesterday,
        title: 'Current Contest 1',
        window_length: 300,
      },
    ],
    future: [
      {
        admission_mode: 'public',
        alias: 'Future-Contest-1',
        description: 'hello contest 1',
        contest_id: 1,
        contestants: 12,
        finish_time: new Date(tomorrow.getTime() + daySeconds),
        last_updated: today,
        organizer: 'omegaup',
        original_finish_time: new Date(tomorrow.getTime() + daySeconds),
        partial_score: false,
        participating: true,
        problemset_id: 1,
        recommended: false,
        start_time: tomorrow,
        title: 'Future Contest 1',
        window_length: 300,
      },
    ],
    past: [
      {
        admission_mode: 'public',
        alias: 'Past-Contest-1',
        description: 'hello contest 1',
        contest_id: 1,
        contestants: 12,
        finish_time: yesterday,
        last_updated: new Date(yesterday.getTime() - daySeconds),
        organizer: 'omegaup',
        original_finish_time: yesterday,
        partial_score: false,
        participating: true,
        problemset_id: 1,
        recommended: false,
        start_time: new Date(yesterday.getTime() - daySeconds),
        title: 'Past Contest 1',
        window_length: 300,
      },
    ],
  };

  it('Should show the current contest list', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
      },
    });

    const currentContestTab = wrapper.findComponent({
      ref: 'currentContestTab',
    });

    expect(currentContestTab.exists()).toBe(true);
    expect(currentContestTab.text()).toContain('Current Contest 1');
  });

  it('Should show the future contest list', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
      },
    });

    const futureContestTab = wrapper.findComponent({
      ref: 'futureContestTab',
    });

    expect(futureContestTab.exists()).toBe(true);
    expect(futureContestTab.text()).toContain('Future Contest 1');
  });

  it('Should show the past contest list', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
      },
    });

    const pastContestTab = wrapper.findComponent({
      ref: 'pastContestTab',
    });

    expect(pastContestTab.exists()).toBe(true);
    expect(pastContestTab.text()).toContain('Past Contest 1');
  });

  it('Should reorder contest list', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
      },
    });

    const dropdownOrderBy = wrapper.findComponent({
      ref: 'dropdownOrderBy'
    }).element as HTMLInputElement;

    dropdownOrderBy.value = T.contestOrderByName;
    await dropdownOrderBy.dispatchEvent(new Event('change'));
    expect(dropdownOrderBy.value).toBe(T.contestOrderByName);

    dropdownOrderBy.value = T.contestOrderByEnds;
    await dropdownOrderBy.dispatchEvent(new Event('change'));
    expect(dropdownOrderBy.value).toBe(T.contestOrderByEnds);

    dropdownOrderBy.value = T.contestOrderByDuration;
    await dropdownOrderBy.dispatchEvent(new Event('change'));
    expect(dropdownOrderBy.value).toBe(T.contestOrderByDuration);

    dropdownOrderBy.value = T.contestOrderByOrganizer;
    await dropdownOrderBy.dispatchEvent(new Event('change'));
    expect(dropdownOrderBy.value).toBe(T.contestOrderByOrganizer);

    dropdownOrderBy.value = T.contestOrderByContestants;
    await dropdownOrderBy.dispatchEvent(new Event('change'));
    expect(dropdownOrderBy.value).toBe(T.contestOrderByContestants);
    
    dropdownOrderBy.value = T.contestOrderBySignedUp;
    await dropdownOrderBy.dispatchEvent(new Event('change'));
    expect(dropdownOrderBy.value).toBe(T.contestOrderBySignedUp);
  });
});
