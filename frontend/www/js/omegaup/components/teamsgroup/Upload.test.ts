import { mount, shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import teamsgroup_Upload from './Upload.vue';

import { CsvTeam } from '../../teamsgroup/edit';

describe('Upload.vue', () => {
  it('Should handle upload teams view with identities', async () => {
    const wrapper = shallowMount(teamsgroup_Upload, {
      propsData: {
        searchResultUsers: [] as types.ListItem[],
      },
    });

    expect(wrapper.text()).toContain(T.groupsUploadCsvFile);

    const identities: CsvTeam[] = [
      {
        username: 'teams:group:team_1',
        name: 'user 1',
        country_id: 'MX',
        state_id: 'QUE',
        gender: 'decline',
        school_name: 'First School',
        usernames: 'user_1;user_2',
      },
    ];
    const identitiesTeams: {
      [team: string]: { username: string; password?: string }[];
    } = {
      'teams:group:team_1': [{ username: 'user_1' }, { username: 'user_2' }],
    };
    await wrapper.setData({ identities, identitiesTeams });
    expect(wrapper.vm.items).toEqual([
      {
        username: 'teams:group:team_1',
        name: 'user 1',
        country_id: 'MX',
        state_id: 'QUE',
        gender: 'decline',
        school_name: 'First School',
        usernames: [{ username: 'user_1' }, { username: 'user_2' }],
      },
    ]);
  });

  it('Should handle download identities to csv file', async () => {
    const wrapper = shallowMount(teamsgroup_Upload, {
      propsData: {
        searchResultUsers: [] as types.ListItem[],
      },
    });

    const identities: CsvTeam[] = [
      {
        username: 'teams:group:team_1',
        name: 'user 1',
        country_id: 'MX',
        state_id: 'QUE',
        gender: 'decline',
        school_name: 'First School',
        usernames: 'user_1;user_2',
      },
    ];
    const identitiesTeams: {
      [team: string]: { username: string; password?: string }[];
    } = {
      'teams:group:team_1': [
        { username: 'user_1', password: '123456' },
        { username: 'user_2', password: '654321' },
      ],
    };
    await wrapper.setData({ identities, identitiesTeams });

    await wrapper.find('button[data-download-csv-button]').trigger('click');

    expect(wrapper.emitted('download-teams')).toEqual([
      [
        [
          {
            country_id: 'MX',
            gender: 'decline',
            name: 'user 1',
            participant_password: '123456',
            participant_username: 'user_1',
            school_name: 'First School',
            state_id: 'QUE',
            username: 'teams:group:team_1',
          },
          {
            country_id: 'MX',
            gender: 'decline',
            name: 'user 1',
            participant_password: '654321',
            participant_username: 'user_2',
            school_name: 'First School',
            state_id: 'QUE',
            username: 'teams:group:team_1',
          },
        ],
      ],
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
