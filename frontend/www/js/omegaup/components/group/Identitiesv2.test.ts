import { mount, shallowMount } from '@vue/test-utils';
import expect from 'expect';
import * as sinon from 'sinon';

import T from '../../lang';

import group_Identitiesv2 from './Identitiesv2.vue';

describe('Identitiesv2.vue', () => {
  it('Should handle identities view', () => {
    const wrapper = shallowMount(group_Identitiesv2, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    expect(wrapper.text()).toContain(T.groupCreateIdentities);
  });

  it('Should handle an invalid csv file', () => {
    const wrapper = mount(group_Identitiesv2, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    const invalid_csv_file = { type: 'text/html', name: 'fake.html' };

    const takeFile = sinon.fake.returns(invalid_csv_file);
    wrapper.setMethods({ takeFile: takeFile });

    const file_input = wrapper.find('file[type=input]');
    file_input.trigger('change');

    expect(wrapper.emitted()['invalid-file']).toBeDefined();
  });

  it('Should handle a valid csv file', () => {
    const wrapper = mount(group_Identitiesv2, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    const invalid_csv_file = { type: 'text/csv', name: 'users.csv' };

    const takeFile = sinon.fake.returns(invalid_csv_file);
    wrapper.setMethods({ takeFile: takeFile });

    const file_input = wrapper.find('file[type=input]');
    file_input.trigger('change');

    expect(wrapper.emitted()['read-csv']).toBeDefined();
  });
});
