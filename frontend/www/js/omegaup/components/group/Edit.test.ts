import { mount, shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import group_Edit from './Edit.vue';

describe('Edit.vue', () => {
  it('Should handle edit view with empty scoreboards and identities', () => {
    const wrapper = shallowMount(group_Edit, {
      propsData: {
        groupAlias: 'Hello',
        groupName: 'Hello omegaUp',
        countries: [{ country_id: 'MX', name: 'Mexico' }],
        isOrganizer: true,
        initialTab: 'members',
        initialIdentities: [],
        initialIdentitiesCsv: [],
        initialScoreboards: [],
      },
    });

    expect(wrapper.text()).toContain('Hello omegaUp');
  });

  it('Should handle edit view getting the subcomponents info', () => {
    const wrapper = mount(group_Edit, {
      propsData: {
        groupAlias: 'Hello',
        groupName: 'Hello omegaUp',
        countries: [{ country_id: 'MX', name: 'Mexico' }],
        isOrganizer: true,
        initialTab: 'members',
        initialIdentities: [],
        initialIdentitiesCsv: [],
        initialScoreboards: [],
      },
    });

    expect(wrapper.text()).toContain(T.groupEditMembers);
  });
});
