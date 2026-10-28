import { mount, shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import user_ManageIdentities from './ManageIdentities.vue';
import T from '../../lang';

describe('ManageIdentities.vue', () => {
  it('Should display empty message when identities list is empty', () => {
    const wrapper = shallowMount(
      user_ManageIdentities as ComponentOptions<Vue>,
      {
        propsData: {
          identities: [],
        },
      },
    );

    expect(wrapper.find('.empty-category').text()).toBe(
      T.profileIdentitiesEmpty,
    );
    expect(wrapper.find('table').exists()).toBe(false);
  });

  it('Should display identities table when identities are provided', () => {
    const wrapper = shallowMount(
      user_ManageIdentities as ComponentOptions<Vue>,
      {
        propsData: {
          identities: [
            { username: 'team:coder1', default_identity: false },
            { username: 'team:coder2', default_identity: false },
          ],
        },
      },
    );

    expect(wrapper.find('.empty-category').exists()).toBe(false);
    expect(wrapper.find('table').exists()).toBe(true);
    const rows = wrapper.findAll('tbody tr');
    expect(rows.length).toBe(2);
    expect(rows.at(0).text()).toContain('team:coder1');
    expect(rows.at(1).text()).toContain('team:coder2');
  });

  it('Should emit add-identity when form is submitted', async () => {
    const wrapper = mount(user_ManageIdentities as ComponentOptions<Vue>, {
      propsData: {
        identities: [],
      },
    });

    await wrapper
      .find('input[data-identity-username]')
      .setValue('team:new_identity');
    await wrapper
      .find('input[data-identity-password]')
      .setValue('secret_password');
    await wrapper.find('form.add-identity-form').trigger('submit');

    expect(wrapper.emitted('add-identity')).toBeDefined();
    expect(wrapper.emitted('add-identity')?.[0]).toEqual([
      {
        username: 'team:new_identity',
        password: 'secret_password',
      },
    ]);
  });
});

