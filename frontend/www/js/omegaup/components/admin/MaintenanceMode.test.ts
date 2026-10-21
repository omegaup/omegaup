import { mount } from '@vue/test-utils';

import T from '../../lang';
import admin_MaintenanceMode from './MaintenanceMode.vue';

const predefinedTemplates = [
  {
    id: 'scheduled_maintenance',
    title: {
      es: 'Mantenimiento programado',
      en: 'Scheduled Maintenance',
      pt: 'Manutenção Programada',
    },
    message: {
      es: 'Mensaje en español',
      en: 'Message in English',
      pt: 'Mensagem em português',
    },
    type: 'warning',
  },
];

const mountMaintenance = (propsData: Record<string, unknown> = {}) =>
  mount(admin_MaintenanceMode, {
    propsData: {
      maintenanceEnabled: false,
      maintenanceMessageEs: '',
      maintenanceMessageEn: '',
      maintenanceMessagePt: '',
      maintenanceType: 'info',
      maintenancePredefinedTemplates: predefinedTemplates,
      preferredLanguage: 'es',
      ...propsData,
    },
    stubs: { 'font-awesome-icon': true },
  });

const firstEventPayload = (
  wrapper: ReturnType<typeof mountMaintenance>,
  eventName: string,
): unknown => {
  const events = wrapper.emitted(eventName) ?? [];
  expect(events).toHaveLength(1);
  return events[0][0];
};

describe('MaintenanceMode.vue', () => {
  it('Should only show the status when maintenance is disabled', () => {
    const wrapper = mountMaintenance();

    expect(wrapper.text()).toContain(T.maintenanceModeInactive);
    expect(wrapper.find('select').exists()).toBe(false);
    expect(wrapper.find('textarea').exists()).toBe(false);
    expect(wrapper.find('button.btn-primary').exists()).toBe(false);
  });

  it('Should show the messages and the save button when enabled', () => {
    const wrapper = mountMaintenance({
      maintenanceEnabled: true,
      maintenanceMessageEs: 'Hola',
      maintenanceMessageEn: 'Hello',
      maintenanceMessagePt: 'Olá',
    });

    expect(wrapper.text()).toContain(T.maintenanceModeActive);
    const messages = wrapper
      .findAll('textarea')
      .wrappers.map(
        (textarea) => (textarea.element as HTMLTextAreaElement).value,
      );
    expect(messages).toEqual(['Hola', 'Hello', 'Olá']);
    expect(wrapper.find('button.btn-primary').text()).toBe(T.wordsSaveChanges);
  });

  it('Should fill the messages and the type from a template', async () => {
    const wrapper = mountMaintenance({ maintenanceEnabled: true });

    await wrapper.find('select').setValue('scheduled_maintenance');
    await wrapper.find('button.btn-primary').trigger('click');

    expect(firstEventPayload(wrapper, 'save-maintenance')).toEqual({
      enabled: true,
      message_es: 'Mantenimiento programado<br>Mensaje en español',
      message_en: 'Scheduled Maintenance<br>Message in English',
      message_pt: 'Manutenção Programada<br>Mensagem em português',
      type: 'warning',
    });
  });

  it('Should emit the current values when saving', async () => {
    const wrapper = mountMaintenance({
      maintenanceEnabled: true,
      maintenanceMessageEs: 'Hola',
      maintenanceMessageEn: 'Hello',
      maintenanceMessagePt: 'Olá',
      maintenanceType: 'danger',
    });

    await wrapper.find('button.btn-primary').trigger('click');

    expect(firstEventPayload(wrapper, 'save-maintenance')).toEqual({
      enabled: true,
      message_es: 'Hola',
      message_en: 'Hello',
      message_pt: 'Olá',
      type: 'danger',
    });
  });

  it('Should emit the toggle event and clear the form when disabling', async () => {
    const wrapper = mountMaintenance({
      maintenanceEnabled: true,
      maintenanceMessageEs: 'Hola',
    });

    await wrapper.find('input[type="checkbox"]').setChecked(false);

    expect(firstEventPayload(wrapper, 'toggle-maintenance')).toBe(false);
    expect(wrapper.find('textarea').exists()).toBe(false);
  });
});
