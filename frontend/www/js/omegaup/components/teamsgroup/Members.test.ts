import { mount, shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';

import teamsgroup_Members from './Members.vue';

describe('Members.vue', () => {
  beforeEach(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
  });

  afterAll(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.removeChild(rootDiv);
    }
  });

  it('Should handle an empty list of members', async () => {
    const teamUsername = 'teams:group_alias:team_1';
    const wrapper = shallowMount(teamsgroup_Members, {
      attachTo: '#root',
      propsData: {
        searchResultUsers: [] as types.ListItem[],
        teamUsername,
        teamsMembers: [] as types.TeamMember[],
      },
    });

    expect(wrapper.find('.card-title').text()).toBe(
      ui.formatString(T.groupEditMembersTitle, {
        username: teamUsername,
      }),
    );

    await wrapper.setData({
      typeaheadContestants: [
        { key: 'user_1', value: 'User 1' },
        { key: 'user_2', value: 'User 2' },
      ],
    });

    expect(wrapper.find('form button[type="submit"]').text()).toBe(
      T.wordsAddMember,
    );
    await wrapper.find('form button[type="submit"]').trigger('click');
    expect(wrapper.emitted('add-members')).toEqual([
      [
        {
          teamUsername,
          usersToAdd: ['user_1', 'user_2'],
        },
      ],
    ]);

    wrapper.destroy();
  });

  it('Should handle a list of members', async () => {
    const teamUsername = 'teams:group_alias:team_1';
    const wrapper = mount(teamsgroup_Members, {
      attachTo: '#root',
      propsData: {
        searchResultUsers: [] as types.ListItem[],
        teamUsername,
        teamsMembers: [
          {
            classname: 'user-rank-unranked',
            name: 'user 1',
            team_alias: teamUsername,
            team_name: 'team 1',
            username: 'user_1',
          },
        ] as types.TeamMember[],
      },
    });

    expect(wrapper.find('table[data-table-members] tbody').text()).toContain(
      'user_1',
    );
    await wrapper.find('button[data-table-remove-member]').trigger('click');
    expect(wrapper.emitted('remove-member')).toEqual([
      [
        {
          teamUsername,
          username: 'user_1',
        },
      ],
    ]);

    wrapper.destroy();
  });

  it('Should handle change password form', async () => {
    const teamUsername = 'teams:group_alias:team_1';
    const wrapper = mount(teamsgroup_Members, {
      attachTo: '#root',
      propsData: {
        searchResultUsers: [] as types.ListItem[],
        teamUsername,
        teamsMembers: [
          {
            classname: 'user-rank-unranked',
            name: 'user 1',
            team_alias: teamUsername,
            team_name: 'team 1',
            username: 'user_1',
          },
        ] as types.TeamMember[],
      },
    });

    await wrapper
      .find('button[data-change-password-identity="user_1"]')
      .trigger('click');

    await wrapper
      .find('.input-group input[type="password"]')
      .setValue('new_pass');

    expect(wrapper.vm.username).toBe('user_1');
    expect(wrapper.vm.password).toBe('new_pass');

    await wrapper
      .find('button[data-save-new-password-identity="user_1"]')
      .trigger('click');

    expect(wrapper.emitted('change-password-identity')).toEqual([
      [
        {
          newPassword: 'new_pass',
          username: 'user_1',
        },
      ],
    ]);
  });
});
