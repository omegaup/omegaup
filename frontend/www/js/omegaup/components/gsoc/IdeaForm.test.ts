import { shallowMount } from '@vue/test-utils';

import IdeaForm from './IdeaForm.vue';

describe('IdeaForm.vue', () => {
  const mockEditions = [
    { edition_id: 1, year: 2024, is_active: true },
    { edition_id: 2, year: 2023, is_active: false },
  ];

  it('Should display create form when no idea prop', () => {
    const wrapper = shallowMount(IdeaForm, {
      propsData: {
        idea: null,
        editions: mockEditions,
      },
    });

    const vm = wrapper.vm as any;
    expect(vm.isEditing).toBe(false);
    expect(vm.idea).toBeNull();
  });

  it('Should display edit form when idea prop provided', () => {
    const mockIdea = {
      idea_id: 1,
      title: 'Test Idea',
      edition_id: 1,
      brief_description: 'Test description',
      status: 'Proposed',
    };

    const wrapper = shallowMount(IdeaForm, {
      propsData: {
        idea: mockIdea,
        editions: mockEditions,
      },
    });

    const vm = wrapper.vm as any;
    expect(vm.isEditing).toBe(true);
    expect(vm.idea).toEqual(mockIdea);
  });

  it('Should disable edition select when editing', () => {
    const mockIdea = {
      idea_id: 1,
      title: 'Test Idea',
      edition_id: 1,
    };

    const wrapper = shallowMount(IdeaForm, {
      propsData: {
        idea: mockIdea,
        editions: mockEditions,
      },
    });

    const editionSelect = wrapper.find('select[disabled]');
    expect(editionSelect.exists()).toBe(true);
  });

  it('Should emit cancel event when cancel button clicked', async () => {
    const wrapper = shallowMount(IdeaForm, {
      propsData: {
        idea: null,
        editions: mockEditions,
      },
    });

    const vm = wrapper.vm as any;
    vm.onCancel();
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted()['cancel']).toBeTruthy();
  });
});
