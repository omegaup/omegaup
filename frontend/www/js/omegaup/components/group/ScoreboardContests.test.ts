import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
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

    expect(wrapper.find('tbody').text()).toContain('Hello omegaUp');
    expect(wrapper.find('tbody').text()).toContain(T.wordsYes);
    expect(wrapper.find('tbody').text()).toContain(2);
  });
});
