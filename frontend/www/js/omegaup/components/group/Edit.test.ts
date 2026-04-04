import { mount, shallowMount } from '@vue/test-utils';

import T from '../../lang';

import { AvailableTabs } from './Edit.vue';
import group_Edit from './Edit.vue';

describe('Edit.vue', () => {
  it('Should handle edit view with empty scoreboards and identities', () => {
    const wrapper = shallowMount(group_Edit, {
      propsData: {
        groupAlias: 'Hello',
        groupName: 'Hello omegaUp',
        countries: [{ country_id: 'MX', name: 'Mexico' }],
        isOrganizer: true,
        tab: AvailableTabs.Members,
        identities: [],
        identitiesCsv: [],
        scoreboards: [],
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
        tab: AvailableTabs.Members,
        identities: [],
        identitiesCsv: [],
        scoreboards: [],
      },
    });

    expect(wrapper.text()).toContain(T.groupEditMembers);
  });
});
