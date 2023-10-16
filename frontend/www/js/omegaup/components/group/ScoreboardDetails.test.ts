import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import group_ScoreboardDetails from './ScoreboardDetails.vue';

describe('ScoreboardDetails.vue', () => {
  it('Should handle group scoreboard details view with two contests', () => {
    const now = Date.now();
    const ranking: types.ScoreboardRanking[] = [
      {
        contests: {
          contest_1: { penalty: 0, points: 300 },
          contest_2: { penalty: 0, points: 200 },
        },
        name: 'user_1',
        total: { penalty: 0, points: 500 },
        username: 'user_1',
      },
      {
        contests: {
          contest_1: { penalty: 0, points: 200 },
          contest_2: { penalty: 0, points: 100 },
        },
        name: 'user_2',
        total: { penalty: 0, points: 100 },
        username: 'user_2',
      },
    ];
    const scoreboard: types.ScoreboardDetails = {
      alias: 'scoreboard',
      create_time: now,
      description: 'Some description',
      group_id: 1,
      group_scoreboard_id: 1,
      name: 'Scoreboard',
    };
    const contest: types.ScoreboardContest = {
      acl_id: 1,
      admission_mode: 'private',
      alias: 'contest_1',
      contest_id: 1,
      description: 'Contest 1',
      feedback: 'none',
      finish_time: new Date(now),
      languages:
        'c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,cs,pas,hs,lua,go,rs,js',
      last_updated: now,
      only_ac: false,
      score_mode: 'partial',
      penalty: '0',
      penalty_calc_policy: 'sum',
      points_decay_factor: 0,
      problemset_id: 1,
      recommended: false,
      rerun_id: 0,
      scoreboard: 100,
      show_scoreboard_after: true,
      start_time: new Date(now),
      submissions_gap: 60,
      title: 'Contest 1',
      urgent: false,
      weight: 1,
      window_length: 0,
    };
    const wrapper = shallowMount(group_ScoreboardDetails, {
      propsData: {
        groupAlias: 'groupAlias',
        scoreboardAlias: 'scoreboardAlias',
        ranking,
        scoreboard,
        contests: [
          contest,
          {
            contest,
            ...{
              alias: 'contest_2',
              title: 'Contest 2',
              description: 'Conctes 2',
              contest_id: 2,
              problemset_id: 2,
            },
          },
        ],
      },
    });

    expect(wrapper.find('.card-header').text()).toBe('Scoreboard');
    expect(wrapper.find('tbody').text()).toContain('user_1');
    expect(wrapper.find('tbody').text()).toContain('user_2');
  });
});
