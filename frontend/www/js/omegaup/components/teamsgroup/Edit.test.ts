import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import { ComponentOptions } from 'vue';

import T from '../../lang';
import * as ui from '../../ui';

import teamsgroup_Edit, { AvailableTabs } from './Edit.vue';
import teamsgroup_FormUpdate from './FormUpdate.vue';
import teamsgroup_Upload from './Upload.vue';

describe('Edit.vue', () => {
  const propsData = {
    alias: 'Hello',
    name: 'Hello omegaUp',
    countries: [{ country_id: 'MX', name: 'Mexico' }],
    isOrganizer: true,
    tab: AvailableTabs.Teams,
    teamsIdentities: [] as types.Identity[],
  };

  it('Should handle edit view with empty teams list', () => {
    const wrapper = shallowMount(teamsgroup_Edit as ComponentOptions<Vue>, {
      propsData,
    });

    expect(wrapper.find('div[class="page-header"]').text()).toBe(
      ui.formatString(T.teamsGroupEditTitleWithName, { name: 'Hello omegaUp' }),
    );
  });

  it('Should change a valid tab', async () => {
    const wrapper = shallowMount(teamsgroup_Edit as ComponentOptions<Vue>, {
      propsData,
    });

    await wrapper.setProps({ tab: AvailableTabs.Edit });
    type TeamsGroupEditInstance = InstanceType<typeof teamsgroup_Edit>;
    const vm = wrapper.vm as unknown as TeamsGroupEditInstance;
    expect(vm.selectedTab).toBe(AvailableTabs.Edit);
  });

  it('Should change an invalid tab', async () => {
    const wrapper = shallowMount(teamsgroup_Edit as ComponentOptions<Vue>, {
      propsData,
    });

    await wrapper.setProps({ tab: 'wrong' });
    type TeamsGroupEditInstance = InstanceType<typeof teamsgroup_Edit>;
    const vm = wrapper.vm as unknown as TeamsGroupEditInstance;

    expect(vm.selectedTab).toBe(AvailableTabs.Teams);
  });

  it('Should change teams identities list', async () => {
    const wrapper = shallowMount(teamsgroup_Edit as ComponentOptions<Vue>, {
      propsData,
    });
    const teamsIdentities: types.Identity[] = [
      {
        username: 'group:team-1',
        name: 'team 1',
        country_id: 'MX',
        school_name: 'First school',
        state_id: 'AGU',
      },
    ];

    await wrapper.setProps({ teamsIdentities });
    type TeamsGroupEditInstance = InstanceType<typeof teamsgroup_Edit>;
    const vm = wrapper.vm as unknown as TeamsGroupEditInstance;

    expect(vm.currentTeamsIdentities).toBe(teamsIdentities);
  });

  it('Should keep the updated contestants count across tabs', async () => {
    const wrapper = shallowMount(teamsgroup_Edit as ComponentOptions<Vue>, {
      propsData: {
        ...propsData,
        numberOfContestants: 3,
        tab: AvailableTabs.Edit,
      },
    });

    expect(wrapper.findComponent(teamsgroup_FormUpdate).props()).toEqual(
      expect.objectContaining({
        numberOfContestants: 3,
      }),
    );

    await wrapper.setProps({ numberOfContestants: 5 });
    await wrapper.find('[data-tab-identities]').trigger('click');

    expect(wrapper.findComponent(teamsgroup_Upload).props()).toEqual(
      expect.objectContaining({
        numberOfContestants: 5,
      }),
    );

    await wrapper.find('[data-tab-edit]').trigger('click');

    expect(wrapper.findComponent(teamsgroup_FormUpdate).props()).toEqual(
      expect.objectContaining({
        numberOfContestants: 5,
      }),
    );
  });
});
