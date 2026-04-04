import { shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';
import T from '../../lang';

import teamsgroup_List from './List.vue';

describe('List.vue', () => {
  it('Should handle an empty list of teams groups', () => {
    const wrapper = shallowMount(teamsgroup_List, {
      propsData: {
        teamsGroups: [] as types.TeamsGroup[],
      },
    });

    expect(wrapper.text()).toContain(T.teamsGroupsCreateNew);
  });

  it('Should handle a list of teams groups', () => {
    const wrapper = shallowMount(teamsgroup_List, {
      propsData: {
        teamsGroups: [
          {
            alias: 'omegaUp',
            create_time: new Date(),
            description: 'hello omegaUp',
            name: 'hello omegaUp',
          },
        ] as types.TeamsGroup[],
      },
    });

    expect(
      wrapper.find('table[data-table-teams-groups] tbody').text(),
    ).toContain('hello');
  });
});
