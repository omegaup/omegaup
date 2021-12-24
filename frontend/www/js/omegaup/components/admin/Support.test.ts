import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import admin_Support from './Support.vue';

describe('Support.vue', () => {
  it('Should handle support page for an specific user', () => {
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

  it('Should handle support page for an specific user with wrong dates', () => {
    const wrapper = shallowMount(admin_Support, {
      propsData: {
        username: 'omegaUp',
        verified: false,
        link: 'any_link',
        lastLogin: 0,
        birthDate: 0,
      },
    });

    expect(wrapper.find('div[data-last-login]').text()).toBe(
      T.userNeverLoggedIn,
    );
    expect(wrapper.find('div[data-birth-date]').text()).toBe('');
  });
});
