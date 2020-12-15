jest.mock('./Identitiesv2.vue');
import { mount, shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import group_Identitiesv2, { readFile } from './Identitiesv2.vue';
const readFileMock = readFile as jest.Mock;

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

    readFileMock.mockResolvedValue({ type: 'text/html', name: 'fake.html' });

    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');

    expect(wrapper.emitted('invalid-file')).toBeDefined();
  });

  it('Should handle a valid csv file', async () => {
    const wrapper = mount(group_Identitiesv2, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    readFileMock.mockResolvedValue({ type: 'text/csv', name: 'users.csv' });

    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');

    expect(wrapper.emitted('read-csv')).toBeDefined();
  });
});
