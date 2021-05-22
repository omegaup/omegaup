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
    identities: [] as types.Identity[],
    identitiesCsv: [] as types.Identity[],
  };

  it('Should handle edit view with empty teams list', () => {
    const wrapper = shallowMount(group_EditTeamsGroup, {
      propsData,
    });

    expect(wrapper.find('div[class="page-header"]').text()).toBe(
      ui.formatString(T.teamsGroupEditTitleWithName, { name: 'Hello omegaUp' }),
    );
  });
});
