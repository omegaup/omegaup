import { mount } from '@vue/test-utils';

import admin_OperationsDashboard from './OperationsDashboard.vue';
import admin_Settings from './Settings.vue';
import { AccessLevel, AvailableModules } from '../../admin/operations_modules';

describe('OperationsDashboard.vue', () => {
  beforeEach(() => {
    window.matchMedia = jest.fn().mockReturnValue({ matches: true });
  });

  it('Should show the home view by default', () => {
    const wrapper = mount(admin_OperationsDashboard, {
      propsData: { accessLevel: AccessLevel.Admin },
    });

    expect(wrapper.findComponent(admin_Settings).exists()).toBe(false);
  });

  it('Should switch sections without losing the layout', async () => {
    const wrapper = mount(admin_OperationsDashboard, {
      propsData: { accessLevel: AccessLevel.Admin },
    });

    await wrapper.setProps({ selectedModule: AvailableModules.Settings });
    expect(wrapper.findComponent(admin_Settings).exists()).toBe(true);
    expect(wrapper.find('nav.admin-dashboard-sidebar').exists()).toBe(true);

    await wrapper.setProps({ selectedModule: AvailableModules.Home });
    expect(wrapper.findComponent(admin_Settings).exists()).toBe(false);
    expect(wrapper.find('nav.admin-dashboard-sidebar').exists()).toBe(true);
  });

  it('Should fall back to home for an unknown module', () => {
    const wrapper = mount(admin_OperationsDashboard, {
      propsData: {
        accessLevel: AccessLevel.Admin,
        selectedModule: 'nonexistent' as AvailableModules,
      },
    });

    expect(wrapper.findComponent(admin_Settings).exists()).toBe(false);
  });

  it('Should not show settings to support users', () => {
    const wrapper = mount(admin_OperationsDashboard, {
      propsData: {
        accessLevel: AccessLevel.Support,
        selectedModule: AvailableModules.Settings,
      },
    });

    expect(wrapper.findComponent(admin_Settings).exists()).toBe(false);
  });

  it('Should forward the ephemeral grader event from settings', () => {
    const wrapper = mount(admin_OperationsDashboard, {
      propsData: {
        accessLevel: AccessLevel.Admin,
        selectedModule: AvailableModules.Settings,
      },
    });

    wrapper
      .findComponent(admin_Settings)
      .vm.$emit('update-ephemeral-grader', false);

    expect(wrapper.emitted('update-ephemeral-grader')).toEqual([[false]]);
  });
});
