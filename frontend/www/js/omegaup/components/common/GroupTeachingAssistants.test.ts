import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import common_GroupTeachingAssistants from './GroupTeachingAssistants.vue';

describe('GroupTeachingAssistants.vue', () => {
  it('Should handle empty group teachig assistants list', () => {
    const wrapper = shallowMount(common_GroupTeachingAssistants, {
      propsData: {
        groupTeachingAssistants: [],
      },
    });

    expect(wrapper.find('.empty-table-message').text()).toBe(
      T.courseEditGroupTeachingAssistantsEmpty,
    );
  });

  it('Should handle runs', async () => {
    const wrapper = shallowMount(common_GroupTeachingAssistants, {
      propsData: {
        groupTeachingAssistants: [
          {
            role: 'teaching_assistant',
            alias: 'group-teaching-assistant',
            name: 'group-teaching-assistant',
          },
        ],
      },
    });
    expect(wrapper.find('table tbody').text()).toContain('teaching_assistant');
    expect(wrapper.find('table tbody').text()).toContain(
      'group-teaching-assistant',
    );
  });
});
