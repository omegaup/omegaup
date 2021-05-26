import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';
import * as ui from '../../ui';

import group_EditTeamsGroup, { AvailableTabs } from './EditTeamsGroup.vue';

describe('EditTeamsGroup.vue', () => {
  const propsData = {
    teamsGroupAlias: 'Hello',
    teamsGroupName: 'Hello omegaUp',
    countries: [{ country_id: 'MX', name: 'Mexico' }],
    isOrganizer: true,
    tab: AvailableTabs.Teams,
    teamsIdentities: [] as types.Identity[],
    teamsIdentitiesCsv: [] as types.Identity[],
  };

  it('Should handle edit view with empty teams list', () => {
    const wrapper = shallowMount(group_EditTeamsGroup, {
      propsData,
    });

    expect(wrapper.find('div[class="page-header"]').text()).toBe(
      ui.formatString(T.teamsGroupEditTitleWithName, { name: 'Hello omegaUp' }),
    );
  });

  it('Should change a valid tab', async () => {
    const wrapper = shallowMount(group_EditTeamsGroup, {
      propsData,
    });

    await wrapper.setProps({ tab: AvailableTabs.Edit });
    expect(wrapper.vm.selectedTab).toBe(AvailableTabs.Edit);
  });

  it('Should change an invalid tab', async () => {
    const wrapper = shallowMount(group_EditTeamsGroup, {
      propsData,
    });

    await wrapper.setProps({ tab: 'wrong' });
    expect(wrapper.vm.selectedTab).toBe(AvailableTabs.Teams);
  });

  it('Should change teams identities and team identities in csv lists', async () => {
    const wrapper = shallowMount(group_EditTeamsGroup, {
      propsData,
    });
    const identities: types.Identity[] = [
      {
        username: 'group:team 1',
        name: 'group:team 1',
        country_id: 'MX',
        school_name: 'First school',
        state_id: 'AGU',
      },
    ];

    await wrapper.setProps({
      teamsIdentities: identities,
      teamsIdentitiesCsv: identities,
    });
    expect(wrapper.vm.currentTeamsIdentities).toBe(identities);
    expect(wrapper.vm.currentTeamsIdentitiesCsv).toBe(identities);
  });
});
