import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import common_TeachingAssistants from './TeachingAssistants.vue';

describe('TeachingAssistants.vue', () => {
  it('Should handle empty teaching assistants list', () => {
    const wrapper = shallowMount(common_TeachingAssistants, {
      propsData: {
        hasParentComponent: false,
        initialTeachingAssistants: [],
      },
    });

    expect(wrapper.find('.empty-table-message').text()).toBe(
      T.courseEditTeachingAssistantsEmpty,
    );
  });

  it('Should handle runs', async () => {
    const wrapper = shallowMount(common_TeachingAssistants, {
      propsData: {
        hasParentComponent: false,
        initialTeachingAssistants: [
          { role: 'teaching_assistant', user_id: 1, username: 'test_user_1' },
          { role: 'teaching_assistant', user_id: 2, username: 'test_user_2' },
        ],
      },
    });
    expect(wrapper.find('table tbody').text()).toContain('teaching_assistant');
  });
});
