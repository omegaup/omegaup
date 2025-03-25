import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import { types } from '../../api_types';

import group_ScoreboardContests from './ScoreboardContests.vue';

describe('ScoreboardContests.vue', () => {
  it('Should handle empty scoreboard contest view', () => {
    const wrapper = shallowMount(group_ScoreboardContests, {
      propsData: {
        scoreboard: 'Hello',
        availableContests: [] as types.ContestListItem[],
        contests: [] as types.ScoreboardContest[],
      },
    });

    expect(wrapper.find('tbody').text()).toBeFalsy();
  });

  it('Should handle scoreboard contest view with one contest', () => {
    const wrapper = shallowMount(group_ScoreboardContests, {
      propsData: {
        scoreboard: 'Hello',
        availableContests: [] as types.ContestListItem[],
        contests: [
          {
            title: 'Hello omegaUp',
            alias: 'omegaUp',
            only_ac: true,
            weight: 2,
          },
        ] as types.ScoreboardContest[],
      },
    });

    expect(wrapper.find('tbody tr td[data-contest-alias]').text()).toBe(
      'Hello omegaUp',
    );
    expect(wrapper.find('tbody tr td[data-contest-only-ac').text()).toBe(
      T.wordsYes,
    );
    expect(wrapper.find('tbody tr td[data-contest-weight]').text()).toBe('2');
  });
});
