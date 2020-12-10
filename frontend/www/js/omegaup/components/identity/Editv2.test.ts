import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import { omegaup } from '../../omegaup';
import { dao } from '../../api_types';

import identity_Editv2 from './Editv2.vue';

describe('Editv2.vue', () => {
  it('Should handle edit identity view with an identity given', () => {
    const wrapper = shallowMount(identity_Editv2, {
      propsData: {
        identity: {
          username: 'hello',
          name: 'hello',
        } as omegaup.Identity,
        countries: [{ country_id: 'mx', name: 'Mexico' }] as dao.Countries[],
      },
    });

    expect(wrapper.text()).toContain('hello');
    // some states of the selected country (MX)
    expect(wrapper.text()).toContain('Jalisco');
    expect(wrapper.text()).toContain('Chiapas');
  });
});
