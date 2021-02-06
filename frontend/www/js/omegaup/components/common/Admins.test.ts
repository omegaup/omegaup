import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import common_Admins from './Admins.vue';

describe('Admins.vue', () => {
  it('Should handle empty admins list', () => {
    const wrapper = shallowMount(common_Admins, {
      propsData: {
        hasParentComponent: false,
        initialAdmins: [],
      },
    });

    expect(wrapper.find('.empty-table-message').text()).toBe(
      T.courseEditAdminsEmpty,
    );
  });

  it('Should handle runs', async () => {
    const wrapper = shallowMount(common_Admins, {
      propsData: {
        hasParentComponent: false,
        initialAdmins: [
          { role: 'owner', user_id: 1, username: 'admin-username' },
          { role: 'site-admin', user_id: 2, username: 'site-admin-username' },
          { role: 'admin', user_id: 3, username: 'user-username' },
        ],
      },
    });
    expect(wrapper.find('table tbody').text()).toContain('owner');
    await wrapper.find('input[name="toggle-site-admins"]').trigger('click');

    expect(wrapper.find('table tbody').text()).toContain('site-admin');
  });
});
