import { mount, shallowMount } from '@vue/test-utils';

import { dao, types } from '../../api_types';

import teamsgroup_Teams from './Teams.vue';

describe('Members.vue', () => {
  it('Should handle an empty list of members and identities', () => {
    const wrapper = shallowMount(teamsgroup_Teams, {
      propsData: {
        teams: [] as types.Identity[],
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
      },
    });

    expect(
      wrapper.find('table[data-table-identities] tbody').text(),
    ).toBeFalsy();
  });

  it('Should handle a list of teams as identities', () => {
    const wrapper = mount(teamsgroup_Teams, {
      propsData: {
        teams: [
          {
            username: 'omegaUp:user',
            name: 'user',
          },
        ] as types.Identity[],
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
      },
    });

    expect(wrapper.find('table[data-table-identities]').text()).toContain(
      'omegaUp:user user',
    );
  });
});
