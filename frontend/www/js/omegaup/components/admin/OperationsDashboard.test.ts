import { mount } from '@vue/test-utils';

import admin_MaintenanceMode from './MaintenanceMode.vue';
import admin_OperationsDashboard from './OperationsDashboard.vue';
import admin_Settings from './Settings.vue';
import { AccessLevel, AvailableModules } from '../../admin/operations_modules';

const mountDashboard = (propsData: Record<string, unknown> = {}) =>
  mount(admin_OperationsDashboard, {
    propsData: { accessLevel: AccessLevel.Admin, ...propsData },
    stubs: { 'font-awesome-icon': true },
  });

type MaintenanceVm = {
  currentMaintenanceEnabled: boolean;
  currentMaintenanceMessageEs: string;
  onToggleMaintenance: (enabled: boolean) => void;
};

describe('OperationsDashboard.vue', () => {
  beforeEach(() => {
    window.matchMedia = jest.fn().mockReturnValue({ matches: true });
  });

  it('Should show the home view by default', () => {
    const wrapper = mountDashboard();

    expect(wrapper.findComponent(admin_Settings).exists()).toBe(false);
    expect(wrapper.findComponent(admin_MaintenanceMode).exists()).toBe(false);
  });

  it('Should switch sections without losing the layout', async () => {
    const wrapper = mountDashboard();

    await wrapper.setProps({ selectedModule: AvailableModules.Settings });
    expect(wrapper.findComponent(admin_Settings).exists()).toBe(true);
    expect(wrapper.find('nav.admin-dashboard-sidebar').exists()).toBe(true);

    await wrapper.setProps({ selectedModule: AvailableModules.Home });
    expect(wrapper.findComponent(admin_Settings).exists()).toBe(false);
    expect(wrapper.find('nav.admin-dashboard-sidebar').exists()).toBe(true);
  });

  it('Should fall back to home for an unknown module', () => {
    const wrapper = mountDashboard({
      selectedModule: 'nonexistent' as AvailableModules,
    });

    expect(wrapper.findComponent(admin_Settings).exists()).toBe(false);
  });

  it('Should show settings and maintenance mode to support users', () => {
    const wrapper = mountDashboard({
      accessLevel: AccessLevel.Support,
      selectedModule: AvailableModules.Settings,
    });

    expect(wrapper.findComponent(admin_Settings).exists()).toBe(true);
    expect(wrapper.findComponent(admin_MaintenanceMode).exists()).toBe(true);
  });

  it('Should show the saved maintenance mode in the settings section', () => {
    const wrapper = mountDashboard({
      selectedModule: AvailableModules.Settings,
      maintenanceMode: {
        enabled: true,
        message_es: 'Hola',
        message_en: 'Hello',
        message_pt: 'Olá',
        type: 'warning',
      },
    });

    const messages = wrapper
      .findAll('textarea')
      .wrappers.map(
        (textarea) => (textarea.element as HTMLTextAreaElement).value,
      );
    expect(messages).toEqual(['Hola', 'Hello', 'Olá']);
  });

  it('Should forward the ephemeral grader event from settings', () => {
    const wrapper = mountDashboard({
      selectedModule: AvailableModules.Settings,
    });

    wrapper
      .findComponent(admin_Settings)
      .vm.$emit('update-ephemeral-grader', false);

    expect(wrapper.emitted('update-ephemeral-grader')).toEqual([[false]]);
  });

  it('Should forward the maintenance events', () => {
    const wrapper = mountDashboard({
      selectedModule: AvailableModules.Settings,
    });
    const settings = {
      enabled: true,
      message_es: 'Hola',
      message_en: 'Hello',
      message_pt: 'Olá',
      type: 'info',
    };
    const maintenance = wrapper.findComponent(admin_MaintenanceMode);

    maintenance.vm.$emit('toggle-maintenance', false);
    maintenance.vm.$emit('save-maintenance', settings);

    expect(wrapper.emitted('toggle-maintenance')).toEqual([[false]]);
    expect(wrapper.emitted('save-maintenance')).toEqual([[settings]]);
  });

  it('Should restore the saved maintenance state when the update fails', async () => {
    const wrapper = mountDashboard({
      selectedModule: AvailableModules.Settings,
      maintenanceMode: {
        enabled: true,
        message_es: 'Hola',
        message_en: 'Hello',
        message_pt: 'Olá',
        type: 'warning',
      },
    });

    // The user turns maintenance off, but the request fails.
    const before = (wrapper.findComponent(admin_MaintenanceMode)
      .vm as unknown) as MaintenanceVm;
    before.onToggleMaintenance(false);
    expect(before.currentMaintenanceEnabled).toBe(false);

    await wrapper.setProps({ maintenanceVersion: 1 });

    const after = (wrapper.findComponent(admin_MaintenanceMode)
      .vm as unknown) as MaintenanceVm;
    expect(after.currentMaintenanceEnabled).toBe(true);
    expect(after.currentMaintenanceMessageEs).toBe('Hola');
  });
});
