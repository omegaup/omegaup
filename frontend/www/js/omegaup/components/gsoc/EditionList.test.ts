import { shallowMount } from '@vue/test-utils';

import { GSoC } from '../../api';
import EditionList from './EditionList.vue';

describe('EditionList.vue', () => {
  beforeEach(() => {
    jest.spyOn(GSoC, 'listEditions').mockResolvedValue({ editions: [] });
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });
  it('Should display editions list', () => {
    const wrapper = shallowMount(EditionList);

    expect(wrapper.exists()).toBe(true);
  });

  it('Should call editEdition when edit button clicked', async () => {
    const mockEditions = [
      {
        edition_id: 1,
        year: 2024,
        is_active: true,
        application_deadline: null,
      },
    ];

    window.alert = jest.fn();

    const wrapper = shallowMount(EditionList);

    const vm = wrapper.vm as any;
    vm.editions = mockEditions;
    await wrapper.vm.$nextTick();

    const editSpy = jest.spyOn(vm, 'editEdition');
    vm.editEdition(mockEditions[0]);

    expect(editSpy).toHaveBeenCalledWith(mockEditions[0]);

    editSpy.mockRestore();
  });
});
