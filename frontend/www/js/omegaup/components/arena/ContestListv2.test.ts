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
        alias: 'Contest-1',
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
      {
        admission_mode: 'public',
        alias: 'Contest-3',
        description: 'hello contest 3',
        contest_id: 3,
        contestants: 15,
        finish_time: new Date(
          tomorrow.setDate(tomorrow.getDate() + daySeconds * 2),
        ),
        last_updated: yesterday,
        organizer: 'alfadown',
        original_finish_time: tomorrow,
        partial_score: false,
        participating: false,
        problemset_id: 1,
        recommended: false,
        start_time: yesterday,
        title: 'Current Contest 3',
        window_length: 300,
      },
      {
        admission_mode: 'public',
        alias: 'Contest-2',
        description: 'hello contest 2',
        contest_id: 2,
        contestants: 5,
        finish_time: new Date(
          tomorrow.setDate(tomorrow.getDate() + daySeconds),
        ),
        last_updated: yesterday,
        organizer: 'lamdaleft',
        original_finish_time: tomorrow,
        partial_score: false,
        participating: true,
        problemset_id: 1,
        recommended: false,
        start_time: yesterday,
        title: 'Current Contest 2',
        window_length: 300,
      },
    ],
    future: [
      {
        admission_mode: 'public',
        alias: 'Contest-1',
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
      {
        admission_mode: 'public',
        alias: 'Contest-3',
        description: 'hello contest 3',
        contest_id: 3,
        contestants: 15,
        finish_time: new Date(tomorrow.getTime() + daySeconds * 3),
        last_updated: today,
        organizer: 'alfadown',
        original_finish_time: new Date(tomorrow.getTime() + daySeconds),
        partial_score: false,
        participating: false,
        problemset_id: 1,
        recommended: false,
        start_time: tomorrow,
        title: 'Future Contest 3',
        window_length: 300,
      },
      {
        admission_mode: 'public',
        alias: 'Contest-2',
        description: 'hello contest 2',
        contest_id: 2,
        contestants: 5,
        finish_time: new Date(tomorrow.getTime() + daySeconds * 2),
        last_updated: today,
        organizer: 'lamdaleft',
        original_finish_time: new Date(tomorrow.getTime() + daySeconds),
        partial_score: false,
        participating: true,
        problemset_id: 1,
        recommended: false,
        start_time: tomorrow,
        title: 'Future Contest 2',
        window_length: 300,
      },
    ],
    past: [
      {
        admission_mode: 'public',
        alias: 'Contest-1',
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
      {
        admission_mode: 'public',
        alias: 'Contest-3',
        description: 'hello contest 3',
        contest_id: 3,
        contestants: 15,
        finish_time: today,
        last_updated: new Date(yesterday.getTime() - daySeconds),
        organizer: 'alfadown',
        original_finish_time: yesterday,
        partial_score: false,
        participating: false,
        problemset_id: 1,
        recommended: false,
        start_time: new Date(yesterday.getTime() - daySeconds),
        title: 'Past Contest 3',
        window_length: 300,
      },
      {
        admission_mode: 'public',
        alias: 'Contest-2',
        description: 'hello contest 2',
        contest_id: 2,
        contestants: 5,
        finish_time: tomorrow,
        last_updated: new Date(yesterday.getTime() - daySeconds),
        organizer: 'lambdaleft',
        original_finish_time: yesterday,
        partial_score: false,
        participating: true,
        problemset_id: 1,
        recommended: false,
        start_time: new Date(yesterday.getTime() - daySeconds),
        title: 'Past Contest 2',
        window_length: 300,
      },
    ],
  };

  it('Should show the current contest list', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
        tab: ContestTab.Current,
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
        tab: ContestTab.Future,
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
        tab: ContestTab.Past,
      },
    });

    const pastContestTab = wrapper.findComponent({
      ref: 'pastContestTab',
    });

    expect(pastContestTab.exists()).toBe(true);
    expect(pastContestTab.text()).toContain('Past Contest 1');
  });

  const dropdownMapping = [
    [{ value: T.contestOrderByTitle }],
    [{ value: T.contestOrderByEnds }],
    [{ value: T.contestOrderByDuration }],
    [{ value: T.contestOrderByOrganizer }],
    [{ value: T.contestOrderByContestants }],
    [{ value: T.contestOrderBySignedUp }],
  ];

  each(dropdownMapping).it(
    'Should show dropdown when "%s" field is selected',
    async (value) => {
      const wrapper = mount(arena_ContestList, {
        propsData: {
          contests,
        },
      });

      const dropdownOrderBy = wrapper.findComponent({
        ref: 'dropdownOrderBy',
      }).element as HTMLInputElement;

      dropdownOrderBy.value = value;
      await dropdownOrderBy.dispatchEvent(new Event('change'));
      expect(dropdownOrderBy.value).toBe(value);
    },
  );

  const orderMapping = [
    [
      {
        field: ContestOrder.Title,
        name: 'title',
        expectedOrder: ['Contest-1', 'Contest-2', 'Contest-3'],
      },
    ],
    [
      {
        field: ContestOrder.Ends,
        name: 'ends',
        expectedOrder: ['Contest-1', 'Contest-2', 'Contest-3'],
      },
    ],
    [
      {
        field: ContestOrder.Duration,
        name: 'duration',
        expectedOrder: ['Contest-1', 'Contest-2', 'Contest-3'],
      },
    ],
    [
      {
        field: ContestOrder.Organizer,
        name: 'organizer',
        expectedOrder: ['Contest-3', 'Contest-2', 'Contest-1'],
      },
    ],
    [
      {
        field: ContestOrder.Contestants,
        name: 'contestants',
        expectedOrder: ['Contest-3', 'Contest-1', 'Contest-2'],
      },
    ],
    [
      {
        field: ContestOrder.SignedUp,
        name: 'signed-up',
        expectedOrder: ['Contest-1', 'Contest-2', 'Contest-3'],
      },
    ],
  ];

  each(orderMapping).it(
    'Should order correctly current contest list when "%s" field is selected',
    async ({ field, name, expectedOrder }) => {
      const wrapper = mount(arena_ContestList, {
        propsData: {
          contests,
          tab: ContestTab.Current,
        },
      });

      await wrapper.find('.b-dropdown').trigger('click');
      await wrapper.find(`a[data-order-by-${name}]`).trigger('click');

      expect(wrapper.vm.currentOrder).toBe(field);
      expect(wrapper.vm.contestList.map((contest) => contest.alias)).toEqual(
        expectedOrder,
      );
    },
  );

  each(orderMapping).it(
    'Should order correct past contest list when "%s" field is selected',
    async ({ field, name, expectedOrder }) => {
      const wrapper = mount(arena_ContestList, {
        propsData: {
          contests,
          tab: ContestTab.Past,
        },
      });

      await wrapper.find('.b-dropdown').trigger('click');
      await wrapper.find(`a[data-order-by-${name}]`).trigger('click');
      expect(wrapper.vm.currentOrder).toBe(field);
      expect(wrapper.vm.contestList.map((contest) => contest.alias)).toEqual(
        expectedOrder,
      );
    },
  );

  each(orderMapping).it(
    'Should order correctly future contest list when "%s" field is selected',
    async ({ field, name, expectedOrder }) => {
      const wrapper = mount(arena_ContestList, {
        propsData: {
          contests,
          tab: ContestTab.Future,
        },
      });

      await wrapper.find('.b-dropdown').trigger('click');
      await wrapper.find(`a[data-order-by-${name}]`).trigger('click');

      expect(wrapper.vm.currentOrder).toBe(field);
      expect(wrapper.vm.contestList.map((contest) => contest.alias)).toEqual(
        expectedOrder,
      );
    },
  );
});
