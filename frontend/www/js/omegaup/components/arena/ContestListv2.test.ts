jest.mock('../../../../third_party/js/diff_match_patch.js');

import T from '../../lang';
import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';

import arena_ContestList, {
  ContestOrder,
  ContestTab,
} from './ContestListv2.vue';
import each from 'jest-each';

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

  it('Should show dropdown', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
      },
    });

    const dropdownOrderBy = wrapper.findComponent({
      ref: 'dropdownOrderBy',
    }).element as HTMLInputElement;

    dropdownOrderBy.value = T.contestOrderByTitle;
    await dropdownOrderBy.dispatchEvent(new Event('change'));
    expect(dropdownOrderBy.value).toBe(T.contestOrderByTitle);

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

  const orderMapping = [
    [{ field: ContestOrder.Title, name: 'title' }],
    [{ field: ContestOrder.Ends, name: 'ends' }],
    [{ field: ContestOrder.Duration, name: 'duration' }],
    [{ field: ContestOrder.Organizer, name: 'organizer' }],
    [{ field: ContestOrder.Contestants, name: 'contestants' }],
    [{ field: ContestOrder.SignedUp, name: 'signed-up' }],
  ];

  each(orderMapping).it(
    'Should order correct current contest list when "%s" field is selected',
    async ({ field, name }) => {
      const wrapper = mount(arena_ContestList, {
        propsData: {
          contests,
          tab: ContestTab.Current,
        },
      });

      await wrapper.find('.b-dropdown').trigger('click');
      await wrapper.find(`a[data-order-by-${name}]`).trigger('click');

      expect(wrapper.vm.currentOrder).toBe(field);
    },
  );

  each(orderMapping).it(
    'Should order correct past contest list when "%s" field is selected',
    async ({ field, name }) => {
      const wrapper = mount(arena_ContestList, {
        propsData: {
          contests,
          tab: ContestTab.Past,
        },
      });

      await wrapper.find('.b-dropdown').trigger('click');
      await wrapper.find(`a[data-order-by-${name}]`).trigger('click');
      expect(wrapper.vm.currentOrder).toBe(field);
    },
  );

  each(orderMapping).it(
    'Should order correct future contest list when "%s" field is selected',
    async ({ field, name }) => {
      const wrapper = mount(arena_ContestList, {
        propsData: {
          contests,
          tab: ContestTab.Future,
        },
      });

      await wrapper.find('.b-dropdown').trigger('click');
      await wrapper.find(`a[data-order-by-${name}]`).trigger('click');

      expect(wrapper.vm.currentOrder).toBe(field);
    },
  );
});
