jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import { types } from '../../api_types';

import common_Scoreboard from './Scoreboard.vue';

describe('Scoreboard.vue', () => {
  it('Should handle scoreboard with limited finishTime', () => {
    const wrapper = mount(common_Scoreboard, {
      propsData: {
        name: 'Scoreboard test',
        finishTime: new Date(Date.now()),
        ranking: [] as types.ScoreboardRankingEntry[],
        problems: [] as types.NavbarProblemsetProblem[],
        lastUpdated: new Date(Date.now()),
      },
    });

    expect(wrapper.find('div[data-arena-wrapper]>div>h2>span').text()).toBe(
      'Scoreboard test',
    );
    expect(wrapper.find('div[data-arena-wrapper]>div>h2>sup').text()).toBe('↻');
    expect(wrapper.find('.clock').text()).toBe('00:00:00');
  });

  it('Should handle scoreboard with unlimited finishTime', () => {
    const wrapper = mount(common_Scoreboard, {
      propsData: {
        name: 'Scoreboard test',
        finishTime: null,
        ranking: [] as types.ScoreboardRankingEntry[],
        problems: [] as types.NavbarProblemsetProblem[],
        lastUpdated: new Date(Date.now()),
      },
    });

    expect(wrapper.find('div[data-arena-wrapper]>div>h2>span').text()).toBe(
      'Scoreboard test',
    );
    expect(wrapper.find('div[data-arena-wrapper]>div>h2>sup').text()).toBe('↻');
    expect(wrapper.find('.clock').text()).toBe('∞');
  });
});
