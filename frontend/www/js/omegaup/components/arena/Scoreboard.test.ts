jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount, shallowMount } from '@vue/test-utils';
import { SocketStatus } from '../../arena/events_socket';
import T from '../../lang';

import { omegaup } from '../../omegaup';

import arena_Scoreboard from './Scoreboard.vue';

const baseScoreboardProps = {
  digitsAfterDecimalPoint: 2,
  title: 'Scoreboard test',
  finishTime: new Date(Date.now()),
  lastUpdated: new Date(Date.now()),
  problems: [
    {
      accepted: 52,
      alias: 'sumas',
      commit: '587cb50672aa364c75e16b638ec7ca7289e24b08',
      difficulty: 0,
      languages:
        'c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,cs,pas,hs,lua,go,rs,js',
      letter: 'A',
      order: 1,
      points: 100,
      submissions: 193,
      title: 'Sumas',
      version: 'dba31432b084856b69bf4f661341561c08d0bbea',
      visibility: 2,
      visits: 0,
    },
  ] as omegaup.Problem[],
  ranking: [
    {
      classname: 'user-rank-master',
      country: 'xx',
      is_invited: false,
      name: 'test_user_0',
      problems: [
        { alias: 'sumas', penalty: 0, percent: 0, points: 0, runs: 2 },
      ],
      total: {
        penalty: 0,
        points: 0,
      },
      username: 'test_user_0',
    },
    {
      classname: 'user-rank-master',
      country: 'xx',
      is_invited: true,
      name: 'test_user_1',
      problems: [
        { alias: 'sumas', penalty: 0, percent: 100, points: 100, runs: 1 },
      ],
      total: {
        penalty: 0,
        points: 0,
      },
      username: 'test_user_1',
    },
  ],
  scoreboardColors: [
    '#FB3F51',
    '#FF5D40',
    '#FFA240',
    '#FFC740',
    '#59EA3A',
    '#37DD6F',
    '#34D0BA',
    '#3AAACF',
    '#8144D6',
    '#CD35D3',
  ],
  showPenalty: false,
};

describe('Scoreboard.vue', () => {
  it('Should handle scoreboard in a contest', async () => {
    const wrapper = shallowMount(arena_Scoreboard, {
      propsData: Object.assign(
        {
          showInvitedUsersFilter: true,
        },
        baseScoreboardProps,
      ),
    });

    expect(wrapper.find('.omegaup-scoreboard table tbody').text()).toContain(
      'test_user_1',
    );

    expect(
      wrapper.find('.omegaup-scoreboard table tbody').text(),
    ).not.toContain('test_user_0');

    await wrapper
      .find('input[type="checkbox"].toggle-contestants')
      .trigger('click');

    expect(wrapper.find('.omegaup-scoreboard table tbody').text()).toContain(
      'test_user_0',
    );

    expect(wrapper.find('.omegaup-scoreboard table tbody').text()).toContain(
      'test_user_1',
    );
  });

  it('Should handle scoreboard in a course', () => {
    const wrapper = shallowMount(arena_Scoreboard, {
      propsData: Object.assign(
        {
          showInvitedUsersFilter: false,
        },
        baseScoreboardProps,
      ),
    });
    // All participants are visible and toggle contestants check is hidden
    expect(wrapper.find('.omegaup-scoreboard table tbody').text()).toContain(
      'test_user_0',
    );

    expect(wrapper.find('.omegaup-scoreboard table tbody').text()).toContain(
      'test_user_1',
    );

    expect(
      wrapper.find('input[type="checkbox"].toggle-contestants').exists(),
    ).toBeFalsy();
  });

  it('Should handle scoreboard with limited finishTime', () => {
    const wrapper = mount(arena_Scoreboard, {
      propsData: baseScoreboardProps,
    });

    expect(wrapper.find('.clock').text()).toBe('00:00:00');
  });

  it('Should handle scoreboard with unlimited finishTime', () => {
    const wrapper = mount(arena_Scoreboard, {
      propsData: { ...baseScoreboardProps, ...{ finishTime: null } },
    });

    expect(wrapper.find('.clock').text()).toBe('∞');
  });

  it('Should handle scoreboard when socket status changes', async () => {
    const wrapper = mount(arena_Scoreboard, {
      propsData: baseScoreboardProps,
    });

    expect(wrapper.find('.omegaup-scoreboard>div>h2>span').text()).toBe(
      'Scoreboard test',
    );
    const socketElement = wrapper.find('.omegaup-scoreboard>div>h2>sup');
    expect(socketElement.text()).toBe(SocketStatus.Waiting);
    expect(socketElement.attributes().class).toBe('socket-status');
    expect(socketElement.attributes().title).toBe(T.socketStatusWaiting);

    await wrapper.setProps({ socketStatus: SocketStatus.Connected });
    expect(socketElement.attributes().class).toBe(
      'socket-status socket-status-ok',
    );
    expect(socketElement.attributes().title).toBe(T.socketStatusConnected);

    await wrapper.setProps({ socketStatus: SocketStatus.Failed });
    expect(socketElement.attributes().class).toBe(
      'socket-status socket-status-error',
    );
    expect(socketElement.attributes().title).toBe(T.socketStatusFailed);
  });
});
