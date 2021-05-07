jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';

import arena_ContestList from './ContestListv2.vue';

describe('ContestListv2.vue', () => {
  const daySeconds = 24 * 60 * 60 * 1000;
  const today = new Date();
  const yesterday = new Date(Date.now() - daySeconds);
  const tomorrow = new Date(Date.now() + daySeconds);

  const contests: types.ContestList = {
    current: [
      {
        admission_mode: 'public',
        alias: 'Current-Contest-1',
        description: 'hello contest 1',
        contest_id: 1,
        finish_time: tomorrow,
        last_updated: yesterday,
        original_finish_time: tomorrow,
        problemset_id: 1,
        recommended: false,
        rerun_id: 0,
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
        finish_time: new Date(tomorrow.getTime() + daySeconds),
        last_updated: today,
        original_finish_time: new Date(tomorrow.getTime() + daySeconds),
        problemset_id: 1,
        recommended: false,
        rerun_id: 0,
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
        finish_time: yesterday,
        last_updated: new Date(yesterday.getTime() - daySeconds),
        original_finish_time: yesterday,
        problemset_id: 1,
        recommended: false,
        rerun_id: 0,
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

    const span = wrapper.find('span');
    expect(span.exists()).toBe(true);

    expect(span.text()).toContain('Current Contest 1');
  });

  it('Should show the future contest list', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
      },
    });

    const span = wrapper.find('span');
    expect(span.exists()).toBe(true);

    expect(span.text()).toContain('Future Contest 1');
  });

  it('Should show the past contest list', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
      },
    });

    const span = wrapper.find('span');
    expect(span.exists()).toBe(true);

    expect(span.text()).toContain('Past Contest 1');
  });
});
