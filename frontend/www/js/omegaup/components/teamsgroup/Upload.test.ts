import { mount, shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import teamsgroup_Upload from './Upload.vue';

describe('Upload.vue', () => {
  it('Should handle upload teams view with identities', async () => {
    const wrapper = shallowMount(teamsgroup_Upload, {
      propsData: {
        searchResultUsers: [] as types.ListItem[],
      },
    });

    expect(wrapper.text()).toContain(T.groupsUploadCsvFile);

    const identities = [
      {
        username: 'team_user_1',
        name: 'user 1',
        country_id: 'MX',
        state_id: 'QUE',
        gender: 'decline',
        school_name: 'First School',
      },
    ] as types.Identity[];
    await wrapper.setData({ identities });
    expect(wrapper.vm.items).toEqual([
      {
        username: 'team_user_1',
        name: 'user 1',
        country_id: 'MX',
        state_id: 'QUE',
        gender: 'decline',
        school_name: 'First School',
        usernames: [],
      },
    ]);
  });

  it('Should handle an invalid csv file', async () => {
    const wrapper = mount(teamsgroup_Upload, {
      propsData: {
        searchResultUsers: [] as types.ListItem[],
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
        searchResultUsers: [] as types.ListItem[],
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
