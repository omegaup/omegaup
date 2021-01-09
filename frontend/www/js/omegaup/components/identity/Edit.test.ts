import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import { dao, types } from '../../api_types';

import identity_Editv2 from './Edit.vue';

describe('Editv2.vue', () => {
  it('Should handle edit identity view with an identity given', () => {
    const wrapper = shallowMount(identity_Editv2, {
      propsData: {
        identity: {
          username: 'hello',
          name: 'hello',
        } as types.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
      },
    });

    expect(wrapper.text()).toContain('hello');
    // some states of the selected country (MX)
    expect(wrapper.text()).toContain('Jalisco');
    expect(wrapper.text()).toContain('Chiapas');
  });
});
