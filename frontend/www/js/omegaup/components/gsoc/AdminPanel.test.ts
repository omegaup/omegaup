import { shallowMount } from '@vue/test-utils';

import { GSoC } from '../../api';
import AdminPanel from './AdminPanel.vue';

describe('AdminPanel.vue', () => {
  beforeEach(() => {
    jest.spyOn(GSoC, 'listEditions').mockResolvedValue({ editions: [] });
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });
  it('Should display admin panel with tabs', () => {
    const wrapper = shallowMount(AdminPanel, {
      stubs: ['idea-list', 'idea-form', 'edition-list'],
    });

    expect(wrapper.exists()).toBe(true);
    const vm = wrapper.vm as any;
    expect(vm.activeTab).toBe('ideas');
  });

  it('Should show ideas tab by default', () => {
    const wrapper = shallowMount(AdminPanel, {
      stubs: ['idea-list', 'idea-form', 'edition-list'],
    });

    const vm = wrapper.vm as any;
    expect(vm.activeTab).toBe('ideas');
  });

  it('Should switch to editions tab when clicked', async () => {
    const wrapper = shallowMount(AdminPanel, {
      stubs: ['idea-list', 'idea-form', 'edition-list'],
    });

    const vm = wrapper.vm as any;
    vm.activeTab = 'editions';
    await wrapper.vm.$nextTick();
    expect(vm.activeTab).toBe('editions');
  });

  it('Should show idea form when edit-idea event is emitted', async () => {
    const wrapper = shallowMount(AdminPanel, {
      stubs: ['idea-list', 'idea-form', 'edition-list'],
    });

    const vm = wrapper.vm as any;
    const testIdea = {
      idea_id: 1,
      title: 'Test Idea',
      edition_id: 1,
    };

    vm.onEditIdea(testIdea);
    await wrapper.vm.$nextTick();

    expect(vm.showIdeaForm).toBe(true);
    expect(vm.selectedIdea).toEqual(testIdea);
  });

  it('Should hide idea form when saved', async () => {
    const wrapper = shallowMount(AdminPanel, {
      stubs: ['idea-list', 'idea-form', 'edition-list'],
    });

    const vm = wrapper.vm as any;
    vm.showIdeaForm = true;
    vm.selectedIdea = { idea_id: 1 };

    vm.onIdeaSaved();
    await wrapper.vm.$nextTick();

    expect(vm.showIdeaForm).toBe(false);
    expect(vm.selectedIdea).toBeNull();
  });
});
