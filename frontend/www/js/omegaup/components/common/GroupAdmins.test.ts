import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import common_GroupAdmins from './GroupAdmins.vue';

describe('GroupAdmins.vue', () => {
  it('Should handle empty group admins list', () => {
    const wrapper = shallowMount(common_GroupAdmins, {
      propsData: {
        groupAdmins: [],
      },
    });

    expect(wrapper.find('.empty-table-message').text()).toBe(
      T.courseEditGroupAdminsEmpty,
    );
  });

  it('Should handle group admins list with data', async () => {
    const wrapper = shallowMount(common_GroupAdmins, {
      propsData: {
        groupAdmins: [
          { role: 'admin', alias: 'group-admin', name: 'group-admin' },
        ],
      },
    });
    expect(wrapper.find('table tbody').text()).toContain('admin');
    expect(wrapper.find('table tbody').text()).toContain('group-admin');
  });
});
