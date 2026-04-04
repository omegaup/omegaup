import { shallowMount } from '@vue/test-utils';

import { dao, types } from '../../api_types';

import identity_Edit from './Edit.vue';

describe('Edit.vue', () => {
  it('Should handle edit identity view with an identity given', () => {
    const wrapper = shallowMount(identity_Edit, {
      propsData: {
        identity: {
          username: 'hello',
          name: 'hello',
        } as types.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
        searchResultSchools: [{ key: 'teams-group', value: 'teams group' }],
      },
    });

    expect(wrapper.text()).toContain('hello');
    // some states of the selected country (MX)
    expect(wrapper.text()).toContain('Jalisco');
    expect(wrapper.text()).toContain('Chiapas');
  });

  it('Should handle correct username for a normal identity', () => {
    const wrapper = shallowMount(identity_Edit, {
      propsData: {
        identity: {
          username: 'group:hello',
          name: 'hello',
        } as types.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
        searchResultSchools: [{ key: 'teams-group', value: 'teams group' }],
      },
    });

    expect(wrapper.find('.input-group-prepend').text()).toBe('group:');
    const input = wrapper.find('input[data-identity-name]')
      .element as HTMLInputElement;
    expect(input.value).toBe('hello');
  });

  it('Should handle correct username for an identity as team', async () => {
    const wrapper = shallowMount(identity_Edit, {
      propsData: {
        identity: {
          username: 'teams:group:hello',
          name: 'hello',
        } as types.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
        searchResultSchools: [{ key: 'teams-group', value: 'teams group' }],
      },
    });

    expect(wrapper.find('.input-group-prepend').text()).toBe('teams:group:');
    const input = wrapper.find('input[data-identity-name]')
      .element as HTMLInputElement;
    expect(input.value).toBe('hello');
  });
});
