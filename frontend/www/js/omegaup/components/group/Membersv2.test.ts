import { mount, shallowMount } from '@vue/test-utils';
import expect from 'expect';

import { omegaup } from '../../omegaup';
import { dao } from '../../api_types';
import T from '../../lang';

import group_Membersv2 from './Membersv2.vue';

describe('Membersv2.vue', () => {
  it('Should handle an empty list of members and identities', () => {
    const wrapper = shallowMount(group_Membersv2, {
      propsData: {
        groupAlias: 'omegaUp',
        identities: [] as omegaup.Identity[],
        identitiesCsv: [] as omegaup.Identity[],
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
      },
    });

    expect(wrapper.text()).toContain(T.wordsAddMember);
  });

  it('Should handle a list of members and identities', () => {
    const wrapper = mount(group_Membersv2, {
      propsData: {
        groupAlias: 'omegaUp',
        identities: [
          {
            username: 'hello',
            name: 'hello',
          },
        ] as omegaup.Identity[],
        identitiesCsv: [
          {
            username: 'omegaUp:user',
            name: 'user',
          },
        ] as omegaup.Identity[],
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
