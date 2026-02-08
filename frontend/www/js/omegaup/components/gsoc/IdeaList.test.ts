import { shallowMount } from '@vue/test-utils';

import { GSoC } from '../../api';
import IdeaList from './IdeaList.vue';

describe('IdeaList.vue', () => {
  beforeEach(() => {
    jest.spyOn(GSoC, 'listEditions').mockResolvedValue({ editions: [] });
    jest.spyOn(GSoC, 'listIdeas').mockResolvedValue({ ideas: [] });
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });
  it('Should display ideas list', () => {
    const wrapper = shallowMount(IdeaList, {
      propsData: {
        isAdmin: false,
      },
    });

    expect(wrapper.exists()).toBe(true);
  });

  it('Should show create button for admin', () => {
    const wrapper = shallowMount(IdeaList, {
      propsData: {
        isAdmin: true,
      },
    });

    const createButton = wrapper.find('.btn-primary');
    expect(createButton.exists()).toBe(true);
  });

  it('Should not show create button for non-admin', () => {
    const wrapper = shallowMount(IdeaList, {
      propsData: {
        isAdmin: false,
      },
    });

    const createButton = wrapper.find('.btn-primary');
    if (createButton.exists()) {
      expect(createButton.text()).not.toContain('Create');
    }
  });

  it('Should emit edit-idea event when edit button clicked', async () => {
    const mockIdea = {
      idea_id: 1,
      title: 'Test Idea',
      edition_id: 1,
      status: 'Proposed',
    };

    const wrapper = shallowMount(IdeaList, {
      propsData: {
        isAdmin: true,
      },
    });

    const vm = wrapper.vm as any;
    vm.ideas = [mockIdea];
    await wrapper.vm.$nextTick();

    vm.editIdea(mockIdea);
    expect(wrapper.emitted()['edit-idea']).toBeTruthy();
    expect(wrapper.emitted()['edit-idea']?.[0]).toEqual([mockIdea]);
  });
});
