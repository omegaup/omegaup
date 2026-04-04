import { mount, shallowMount } from '@vue/test-utils';

import { dao, types } from '../../api_types';
import T from '../../lang';

import teamsgroup_Teams, { AvailableForms } from './Teams.vue';
import identity_Edit from '../identity/Edit.vue';

describe('Teams.vue', () => {
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

  const propsData: {
    teams: types.Identity[];
    countries: dao.Countries[];
    searchResultUsers: types.ListItem[];
    searchResultSchools: types.SchoolListItem[];
  } = {
    teams: [
      {
        username: 'omegaUp:user',
        name: 'user',
      },
    ],
    countries: [{ country_id: 'mx', name: 'Mexico' }],
    searchResultUsers: [{ key: 'user', value: 'username' }],
    searchResultSchools: [{ key: 1, value: 'school name' }],
  };

  it('Should handle an empty list of members and identities', () => {
    const wrapper = shallowMount(teamsgroup_Teams, {
      propsData: {
        teams: [] as types.Identity[],
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
      },
    });

    expect(wrapper.find('.empty-table-message').text()).toBe(
      T.teamsGroupTeamsEmptyList,
    );
  });

  it('Should handle a list of teams as identities', () => {
    const wrapper = mount(teamsgroup_Teams, {
      propsData,
    });

    expect(wrapper.find('table[data-table-identities]').text()).toContain(
      'omegaUp:user user',
    );
  });

  it('Should handle edit identity form', async () => {
    const wrapper = mount(teamsgroup_Teams, {
      attachTo: '#root',
      propsData,
    });

    await wrapper
      .find('button[data-edit-identity="omegaUp:user"]')
      .trigger('click');

    expect(wrapper.vm.identity).toEqual({
      username: 'omegaUp:user',
      name: 'user',
    });
    expect(wrapper.vm.formToShow).toBe(AvailableForms.Edit);
    expect(wrapper.vm.username).toBe('omegaUp:user');

    await wrapper.find('button[type="reset"]').trigger('click');

    expect(wrapper.vm.identity).toBe(null);
    expect(wrapper.vm.formToShow).toBe(AvailableForms.None);
    expect(wrapper.vm.username).toBe(null);

    await wrapper
      .find('button[data-edit-identity="omegaUp:user"]')
      .trigger('click');

    const identityEditWrapper = wrapper.findComponent(identity_Edit);

    await identityEditWrapper.setData({
      selectedIdentity: {
        name: 'updated user',
      },
    });

    await identityEditWrapper
      .find('button[data-update-identity]')
      .trigger('click');
    expect(wrapper.emitted('edit-identity-team')).toEqual([
      [
        {
          identity: {
            classname: '',
            country_id: 'MX',
            gender: '',
            name: 'updated user',
            school: 'school name',
            state_id: '',
            username: 'omegaUp:user',
          },
          originalUsername: 'omegaUp:user',
        },
      ],
    ]);
  });
});
