import { mount, shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import group_Identitiesv2 from './Identitiesv2.vue';

describe('Identitiesv2.vue', () => {
  it('Should handle identities view', () => {
    const wrapper = shallowMount(group_Identitiesv2, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    expect(wrapper.text()).toContain(T.groupsUploadCsvFile);
  });

  it('Should handle an invalid csv file', async () => {
    const wrapper = mount(group_Identitiesv2, {
      propsData: {
        groupAlias: 'Hello',
      },
    });
    const invalidFile = { type: 'text/html', name: 'fake.html' };
    const mockMethod = jest
      .spyOn(wrapper.vm, 'readFile')
      .mockImplementation(() => invalidFile);
    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');
    expect(mockMethod).toHaveBeenCalled();
    expect(wrapper.emitted('invalid-file')).toBeDefined();
  });

  it('Should handle a valid csv file', async () => {
    const wrapper = mount(group_Identitiesv2, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    const validFile = { type: 'text/csv', name: 'users.csv' };
    const mockMethod = jest
      .spyOn(wrapper.vm, 'readFile')
      .mockImplementation(() => validFile);
    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');
    expect(mockMethod).toHaveBeenCalled();
    expect(wrapper.emitted('read-csv')).toBeDefined();
  });
});
