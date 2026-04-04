import { shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';
import T from '../../lang';

import group_List from './List.vue';

describe('List.vue', () => {
  it('Should handle an empty list of groups', () => {
    const wrapper = shallowMount(group_List, {
      propsData: {
        groups: [] as types.Group[],
      },
    });

    expect(wrapper.text()).toContain(T.groupsCreateNew);
  });

  it('Should handle a list of groups', () => {
    const wrapper = shallowMount(group_List, {
      propsData: {
        groups: [
          {
            alias: 'omegaUp',
            create_time: new Date(),
            description: 'hello omegaUp',
            name: 'hello omegaUp',
          },
        ] as types.Group[],
      },
    });

    expect(wrapper.find('table[data-table-groups] tbody').text()).toContain(
      'hello',
    );
  });
});
