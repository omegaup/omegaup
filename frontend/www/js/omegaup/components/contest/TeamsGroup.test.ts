import { shallowMount, mount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import contest_TeamsGroup from './TeamsGroup.vue';

describe('TeamsGroup.vue', () => {
  beforeAll(() => {
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

  const teamsGroup: types.ContestGroup = { alias: 'alias', name: 'name' };

  it('Should handle empty teams groups', () => {
    const wrapper = shallowMount(contest_TeamsGroup, {
      propsData: {
        teamsGroup,
      },
    });

    expect(wrapper.text()).toContain(T.contestEditTeamsGroupReplace);
  });

  it('Should submit a new teams group', async () => {
    const wrapper = mount(contest_TeamsGroup, {
      attachTo: '#root',
      propsData: {
        teamsGroup,
        searchResultTeamsGroups: [
          { key: 'teams-group', value: 'teams group' },
        ] as types.ListItem[],
      },
    });
    await wrapper.setData({
      typeaheadGroup: { key: 'teams-group', value: 'teams group' },
    });
    await wrapper.find('button[type="submit"]').trigger('click');
    expect(wrapper.emitted('replace-teams-group')).toEqual([
      [{ alias: 'teams-group', name: 'teams group' }],
    ]);

    wrapper.destroy();
  });
});
