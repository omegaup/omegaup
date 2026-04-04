import { types } from '../../api_types';
import { shallowMount, mount } from '@vue/test-utils';

import T from '../../lang';

import common_TeachingAssistants from './TeachingAssistants.vue';

describe('TeachingAssistants.vue', () => {
  beforeEach(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
  });

  afterAll(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.removeChild(rootDiv);
    }
  });

  it('Should handle empty teaching assistants list', () => {
    const wrapper = shallowMount(common_TeachingAssistants, {
      propsData: {
        teachingAssistants: [],
        searchResultUsers: [],
      },
    });

    expect(wrapper.find('.empty-table-message').text()).toBe(
      T.courseEditTeachingAssistantsEmpty,
    );
  });

  it('Should handle teaching assistants list', async () => {
    const wrapper = shallowMount(common_TeachingAssistants, {
      propsData: {
        teachingAssistants: [
          { role: 'teaching_assistant', user_id: 1, username: 'test_user_1' },
          { role: 'teaching_assistant', user_id: 2, username: 'test_user_2' },
        ],
        searchResultUsers: [],
      },
    });
    expect(wrapper.find('table tbody').text()).toContain('teaching_assistant');
  });

  it('Should handle teaching assistants list when is updated', async () => {
    const wrapper = mount(common_TeachingAssistants, {
      propsData: {
        teachingAssistants: [
          { role: 'teaching_assistant', user_id: 1, username: 'test_user_1' },
          { role: 'teaching_assistant', user_id: 2, username: 'test_user_2' },
        ],
        searchResultUsers: [],
      },
    });
    expect(wrapper.find('table tbody').text()).toContain('test_user_1');
    expect(wrapper.find('table tbody').text()).toContain('test_user_2');
    expect(wrapper.find('table tbody').text()).not.toContain('test_user_3');
    await wrapper.setProps({
      teachingAssistants: [
        { role: 'teaching_assistant', user_id: 3, username: 'test_user_3' },
        { role: 'teaching_assistant', user_id: 2, username: 'test_user_2' },
      ],
    });
    expect(wrapper.find('table tbody').text()).toContain('test_user_3');
    expect(wrapper.find('table tbody').text()).toContain('test_user_2');
    expect(wrapper.find('table tbody').text()).not.toContain('test_user_1');
  });

  it('Should handle onSubmit event', async () => {
    const wrapper = mount(common_TeachingAssistants, {
      propsData: {
        attachTo: '#root',
        teachingAssistants: [
          { role: 'teaching_assistant', user_id: 1, username: 'test_user_1' },
          { role: 'teaching_assistant', user_id: 2, username: 'test_user_2' },
        ],
        searchResultUsers: [
          { key: 'test_user_1', value: 'test user 1' },
          { key: 'test_user_2', value: 'test user 2' },
        ] as types.ListItem[],
      },
    });
    await wrapper.setData({ username: wrapper.vm.searchResultUsers[0] });
    const button = wrapper.find('button[type="submit"]');
    await button.trigger('submit');
    expect(wrapper.emitted('add-teaching-assistant')).toEqual([
      ['test_user_1'],
    ]);
    wrapper.destroy();
  });

  it('Should handle onRemove event', async () => {
    const wrapper = shallowMount(common_TeachingAssistants, {
      propsData: {
        teachingAssistants: [
          { role: 'teaching_assistant', user_id: 1, username: 'test_user_1' },
        ],
        searchResultUsers: [],
      },
    });
    const button = wrapper.find('button[type="button"]');
    await button.trigger('click');
    expect(wrapper.emitted('remove-teaching-assistant')).toEqual([
      ['test_user_1'],
    ]);
  });
});
