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

  it('Should show the notice only when enabled', () => {
    const disabled = mountMaintenance();
    expect(disabled.text()).not.toContain(T.maintenanceModeNotice);

    const enabled = mountMaintenance({ maintenanceEnabled: true });
    expect(enabled.text()).toContain(T.maintenanceModeNotice);
  });

  it('Should select the message type with the buttons', async () => {
    const wrapper = mountMaintenance({ maintenanceEnabled: true });

    const dangerButton = wrapper.find('button.btn-outline-danger');
    expect(dangerButton.attributes('aria-pressed')).toBe('false');

    await dangerButton.trigger('click');
    expect(dangerButton.attributes('aria-pressed')).toBe('true');

    await wrapper.find('button.btn-primary').trigger('click');
    expect(firstEventPayload(wrapper, 'save-maintenance')).toMatchObject({
      type: 'danger',
    });
  });

  it('Should preview the message in the preferred language', () => {
    const wrapper = mountMaintenance({
      maintenanceEnabled: true,
      maintenanceMessageEs: 'Titulo<br>Detalle',
      maintenanceMessageEn: 'Title<br>Detail',
      preferredLanguage: 'en',
    });

    const lines = wrapper
      .findAll('[data-maintenance-preview] > div')
      .wrappers.map((line) => line.text());
    expect(lines).toEqual(['Title', 'Detail']);
  });

  it('Should not render HTML in the preview', () => {
    const wrapper = mountMaintenance({
      maintenanceEnabled: true,
      maintenanceMessageEs: '<b>Hola</b>',
    });

    const preview = wrapper.find('[data-maintenance-preview]');
    expect(preview.text()).toBe('<b>Hola</b>');
    expect(preview.find('b').exists()).toBe(false);
  });

  it('Should hide the preview when there is no message', () => {
    const wrapper = mountMaintenance({ maintenanceEnabled: true });

    expect(wrapper.find('[data-maintenance-preview]').exists()).toBe(false);
  });

  it('Should restore the saved values when cancelling', async () => {
    const wrapper = mountMaintenance({
      maintenanceEnabled: true,
      maintenanceMessageEs: 'Hola',
    });

    await wrapper.find('textarea').setValue('Cambio sin guardar');
    await wrapper.find('[data-maintenance-cancel]').trigger('click');

    expect(
      (wrapper.find('textarea').element as HTMLTextAreaElement).value,
    ).toBe('Hola');
    expect(wrapper.emitted('save-maintenance')).toBeUndefined();
  });

  it('Should hide the fields when cancelling a switch that was not saved', async () => {
    const wrapper = mountMaintenance();

    await wrapper.find('input[type="checkbox"]').setChecked(true);
    expect(wrapper.find('textarea').exists()).toBe(true);

    await wrapper.find('[data-maintenance-cancel]').trigger('click');
    expect(wrapper.find('textarea').exists()).toBe(false);
  });
});
