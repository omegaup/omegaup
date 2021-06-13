import { mount, shallowMount } from '@vue/test-utils';

import T from '../../lang';

import teamsgroup_Upload from './Upload.vue';

describe('Upload.vue', () => {
  it('Should handle upload teams view', () => {
    const wrapper = shallowMount(teamsgroup_Upload, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    expect(wrapper.text()).toContain(T.groupsUploadCsvFile);
  });

  it('Should handle an invalid csv file', async () => {
    const wrapper = mount(teamsgroup_Upload, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    const invalidFile = new File([''], 'fake.html', { type: 'text/html' });
    const mockReadFileMethod = jest
      .spyOn(wrapper.vm, 'readFile')
      .mockImplementation(() => invalidFile);
    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');
    expect(mockReadFileMethod).toHaveBeenCalled();
    expect(wrapper.emitted('invalid-file')).toBeDefined();
    mockReadFileMethod.mockRestore();
  });

  it('Should handle a valid csv file', async () => {
    const wrapper = mount(teamsgroup_Upload, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    const validFile = new File([''], 'users.csv', { type: 'text/csv' });
    const mockReadFileMethod = jest
      .spyOn(wrapper.vm, 'readFile')
      .mockImplementation(() => validFile);
    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');
    expect(mockReadFileMethod).toHaveBeenCalled();
    expect(wrapper.emitted('read-csv')).toBeDefined();
    mockReadFileMethod.mockRestore();
  });
});
