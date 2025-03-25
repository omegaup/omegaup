import { mount, shallowMount } from '@vue/test-utils';

import { dao, types } from '../../api_types';
import T from '../../lang';

import group_Members from './Members.vue';

describe('Members.vue', () => {
  it('Should handle an empty list of members and identities', () => {
    const wrapper = shallowMount(group_Members, {
      propsData: {
        groupAlias: 'omegaUp',
        identities: [] as types.Identity[],
        identitiesCsv: [] as types.Identity[],
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
      },
    });

    expect(wrapper.text()).toContain(T.wordsAddMember);
  });

  it('Should handle a list of members and identities', () => {
    const wrapper = mount(group_Members, {
      propsData: {
        groupAlias: 'omegaUp',
        identities: [
          {
            username: 'hello',
            name: 'hello',
          },
        ] as types.Identity[],
        identitiesCsv: [
          {
            username: 'omegaUp:user',
            name: 'user',
          },
        ] as types.Identity[],
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
      },
    });

    expect(wrapper.find('table[data-table-members] tbody').text()).toContain(
      'hello',
    );
    expect(wrapper.find('table[data-table-identities] tbody').text()).toContain(
      'omegaUp:user user',
    );
  });
});
