import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import admin_MaintenanceMode from './MaintenanceMode.vue';
import admin_Support from './Support.vue';

describe('Support.vue', () => {
  it('Should handle support page for users without a birth date', () => {
    const wrapper = shallowMount(admin_Support, {
      propsData: {
        username: 'omegaUp',
        verified: false,
        link: 'any_link',
        lastLogin: null,
        birthDate: null,
      },
    });

    expect(wrapper.find('div[data-last-login]').text()).toBe(
      T.userNeverLoggedIn,
    );
    expect(wrapper.find('div[data-birth-date]').text()).toBe('');
  });

  it('Should forward the maintenance events to the parent', () => {
    const wrapper = shallowMount(admin_Support, {
      propsData: {
        username: 'omegaUp',
        verified: false,
        link: 'any_link',
        lastLogin: null,
        birthDate: null,
      },
    });
    const maintenance = wrapper.findComponent(admin_MaintenanceMode);
    const settings = {
      enabled: true,
      message_es: 'Hola',
      message_en: 'Hello',
      message_pt: 'Olá',
      type: 'info',
    };

    maintenance.vm.$emit('toggle-maintenance', false);
    maintenance.vm.$emit('save-maintenance', settings);

    expect(wrapper.emitted('toggle-maintenance')).toEqual([[false]]);
    expect(wrapper.emitted('save-maintenance')).toEqual([[settings]]);
  });
});
