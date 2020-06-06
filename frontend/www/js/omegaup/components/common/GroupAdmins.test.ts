import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import common_GroupAdmins from './GroupAdmins.vue';

describe('GroupAdmins.vue', () => {
  it('Should handle empty admins list', () => {
    const wrapper = shallowMount(common_GroupAdmins, {
      propsData: {
        hasParentComponent: false,
        initialGroups: [],
      },
    });

    expect(wrapper.find('.empty-table-message').text()).toBe(
      T.courseEditGroupAdminsEmpty,
    );
  });

  it('Should handle runs', async () => {
    const wrapper = shallowMount(common_GroupAdmins, {
      propsData: {
        hasParentComponent: false,
        initialGroups: [
          { role: 'admin', alias: 'group-admin', name: 'group-admin' },
        ],
      },
    });
    expect(wrapper.find('table tbody').isEmpty()).toBe(false);

    expect(wrapper.find('table tbody').text()).toContain('group-admin');
  });
});
