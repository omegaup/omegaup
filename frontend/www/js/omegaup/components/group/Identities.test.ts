import { mount, shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { ComponentOptions } from 'vue';
import group_Identities from './Identities.vue';

describe('Identities.vue', () => {
  it('Should handle identities view', () => {
    const wrapper = shallowMount(group_Identities as ComponentOptions<Vue>, {
      propsData: {
        groupAlias: 'Hello',
        isOrganizer: true,
        hasVisitedSection: true,
      },
    });

    expect(wrapper.text()).toContain(T.groupsUploadCsvFile);
  });

  it('Should handle an invalid csv file', async () => {
    const wrapper = mount(group_Identities as ComponentOptions<Vue>, {
      propsData: {
        groupAlias: 'Hello',
        isOrganizer: true,
        hasVisitedSection: true,
      },
    });

    const invalidFile = new File([''], 'fake.html', { type: 'text/html' });
    const mockReadFileMethod = jest
      .spyOn((wrapper.vm as any), 'readFile')
      .mockImplementation(() => invalidFile);
    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');
    expect(mockReadFileMethod).toHaveBeenCalled();
    expect(wrapper.emitted('invalid-file')).toBeDefined();
    mockReadFileMethod.mockRestore();
  });

  it('Should handle a valid csv file', async () => {
    const wrapper = mount(group_Identities as ComponentOptions<Vue>, {
      propsData: {
        groupAlias: 'Hello',
        isOrganizer: true,
        hasVisitedSection: true,
      },
    });

    const validFile = new File([''], 'users.csv', { type: 'text/csv' });
    const mockReadFileMethod = jest
      .spyOn((wrapper.vm as any), 'readFile')
      .mockImplementation(() => validFile);
    const fileInput = wrapper.find('input[type=file]');
    await fileInput.trigger('change');
    expect(mockReadFileMethod).toHaveBeenCalled();
    expect(wrapper.emitted('read-csv')).toBeDefined();
    mockReadFileMethod.mockRestore();
  });

  it('Should handle the view for restricted users', () => {
    const wrapper = mount(group_Identities as ComponentOptions<Vue>, {
      propsData: {
        groupAlias: 'Hello',
        isOrganizer: false,
        hasVisitedSection: true,
      },
    });

    expect(wrapper.text()).toContain('soporte@omegaup.com ');
  });
});
