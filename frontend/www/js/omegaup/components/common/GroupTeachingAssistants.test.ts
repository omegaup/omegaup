import { shallowMount, mount } from '@vue/test-utils';

import T from '../../lang';

import common_GroupTeachingAssistants from './GroupTeachingAssistants.vue';

describe('GroupTeachingAssistants.vue', () => {
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

  it('Should handle empty group teachig assistants list', () => {
    const wrapper = shallowMount(common_GroupTeachingAssistants, {
      propsData: {
        groupTeachingAssistants: [],
        searchResultGroups: [],
      },
    });

    expect(wrapper.find('.empty-table-message').text()).toBe(
      T.courseEditGroupTeachingAssistantsEmpty,
    );
  });

  it('Should handle group teaching assistants list', async () => {
    const wrapper = shallowMount(common_GroupTeachingAssistants, {
      propsData: {
        groupTeachingAssistants: [
          {
            role: 'teaching_assistant',
            alias: 'group-teaching-assistant',
            name: 'group-teaching-assistant',
          },
        ],
        searchResultGroups: [],
      },
    });
    expect(wrapper.find('table tbody').text()).toContain('teaching_assistant');
    expect(wrapper.find('table tbody').text()).toContain(
      'group-teaching-assistant',
    );
  });

  it('Should handle group teaching assistants list when is updated', async () => {
    const wrapper = mount(common_GroupTeachingAssistants, {
      propsData: {
        groupTeachingAssistants: [
          {
            role: 'teaching_assistant',
            alias: 'group-teaching-assistant_1',
            name: 'group-teaching-assistant_1',
          },
          {
            role: 'teaching_assistant',
            alias: 'group-teaching-assistant_2',
            name: 'group-teaching-assistant_2',
          },
        ],
        searchResultGroups: [],
      },
    });
    expect(wrapper.find('table tbody').text()).toContain(
      'group-teaching-assistant_1',
    );
    expect(wrapper.find('table tbody').text()).toContain(
      'group-teaching-assistant_2',
    );
    expect(wrapper.find('table tbody').text()).not.toContain(
      'group-teaching-assistant_3',
    );
    await wrapper.setProps({
      groupTeachingAssistants: [
        {
          role: 'teaching_assistant',
          alias: 'group-teaching-assistant_3',
          name: 'group-teaching-assistant_3',
        },
        {
          role: 'teaching_assistant',
          alias: 'group-teaching-assistant_2',
          name: 'group-teaching-assistant_2',
        },
      ],
    });
    expect(wrapper.find('table tbody').text()).toContain(
      'group-teaching-assistant_3',
    );
    expect(wrapper.find('table tbody').text()).toContain(
      'group-teaching-assistant_2',
    );
    expect(wrapper.find('table tbody').text()).not.toContain(
      'group-teaching-assistant_1',
    );
  });

  it('Should handle onRemove event', async () => {
    const wrapper = shallowMount(common_GroupTeachingAssistants, {
      propsData: {
        groupTeachingAssistants: [
          {
            role: 'teaching_assistant',
            alias: 'group_teaching_assistant_1',
            name: 'group_teaching_assistant_1',
          },
        ],
        searchResultGroups: [],
      },
    });
    const button = wrapper.find('button[type="button"]');
    await button.trigger('click');
    expect(wrapper.emitted('remove-group-teaching-assistant')).toEqual([
      ['group_teaching_assistant_1'],
    ]);
  });
});
