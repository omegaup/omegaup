import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import type { types } from '../../api_types';

import AdminRoles from './Roles.vue';

describe('Roles.vue', () => {
  const roles = ([
    { name: 'Admin', value: false },
    { name: 'Reviewer', value: true },
  ] as unknown) as types.UserRole[];
  const groups = ([
    { alias: 'mentors', name: 'Mentors', value: false },
    { alias: 'devs', name: 'Developers', value: true },
  ] as unknown) as types.Group[];

  it('Should render roles and groups correctly', () => {
    const wrapper = shallowMount(AdminRoles as ComponentOptions<Vue>, {
      propsData: {
        roles,
        groups,
      },
    });

    expect(wrapper.text()).toContain('Admin');
    expect(wrapper.text()).toContain('Reviewer');
    expect(wrapper.text()).toContain('Mentors');
    expect(wrapper.text()).toContain('Developers');
  });

  it('Should emit change-role event when a role checkbox changes', async () => {
    const wrapper = shallowMount(AdminRoles as ComponentOptions<Vue>, {
      propsData: {
        roles,
        groups,
      },
    });

    const roleInput = wrapper.findAll('input[type="checkbox"]').at(0);
    const inputElement = roleInput.element as HTMLInputElement;
    inputElement.checked = true;
    await roleInput.trigger('change');

    expect(wrapper.emitted('change-role')).toEqual([
      [
        {
          value: roles[0],
          selected: true,
        },
      ],
    ]);
  });

  it('Should emit change-group event when a group checkbox changes', async () => {
    const wrapper = shallowMount(AdminRoles as ComponentOptions<Vue>, {
      propsData: {
        roles,
        groups,
      },
    });

    // 2 role checkboxes + first group checkbox = index 2
    const groupInput = wrapper.findAll('input[type="checkbox"]').at(2);
    const inputElement = groupInput.element as HTMLInputElement;
    inputElement.checked = true;
    await groupInput.trigger('change');

    expect(wrapper.emitted('change-group')).toEqual([
      [
        {
          value: groups[0],
          selected: true,
        },
      ],
    ]);
  });
});
