import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';

import { dao, types } from '../../api_types';

import identity_Edit from './Edit.vue';

describe('Edit.vue', () => {
  const Component = identity_Edit as ComponentOptions<Vue>;

  it('Should handle edit identity view with an identity given', () => {
    const wrapper = shallowMount(Component, {
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
    const wrapper = shallowMount(Component, {
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
    const wrapper = shallowMount(Component, {
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

  it('Should update username when identityName setter is invoked', async () => {
    const wrapper = shallowMount(Component, {
      propsData: {
        identity: {
          username: 'group:oldname',
          name: 'Old Name',
        } as types.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
        searchResultSchools: [{ key: 'school-1', value: 'School One' }],
      },
    });

    const vm = wrapper.vm as any;
    vm.identityName = 'newname';
    expect(vm.selectedIdentity.username).toBe('group:newname');
  });

  it('Should emit edit-identity-member on form submit', async () => {
    const wrapper = shallowMount(Component, {
      propsData: {
        identity: {
          username: 'group:hello',
          name: 'Hello User',
        } as types.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
        searchResultSchools: [{ key: 'school-1', value: 'School One' }],
      },
    });

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('edit-identity-member')).toBeTruthy();
    expect(wrapper.emitted('edit-identity-member')?.[0][0]).toEqual({
      originalUsername: 'group:hello',
      identity: expect.objectContaining({
        username: 'group:hello',
        school: 'School One',
      }),
    });
  });

  it('Should emit cancel on cancel button click', async () => {
    const wrapper = shallowMount(Component, {
      propsData: {
        identity: {
          username: 'group:hello',
          name: 'Hello User',
        } as types.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
        searchResultSchools: [],
      },
    });

    await wrapper.find('button[type="reset"]').trigger('click');
    expect(wrapper.emitted('cancel')).toBeTruthy();
  });

  it('Should update selectedIdentity when identity prop changes', async () => {
    const wrapper = shallowMount(Component, {
      propsData: {
        identity: {
          username: 'group:initial',
          name: 'Initial Name',
        } as types.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
        searchResultSchools: [],
      },
    });

    await wrapper.setProps({
      identity: {
        username: 'group:updated',
        name: 'Updated Name',
      } as types.Identity,
    });

    const vm = wrapper.vm as any;
    expect(vm.selectedIdentity.username).toBe('group:updated');
    expect(vm.selectedIdentity.name).toBe('Updated Name');
  });
});
