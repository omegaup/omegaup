import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';
import T from '../../lang';

import course_Clarifications from './Clarifications.vue';

describe('CourseClarifications.vue', () => {
  it('Should display correct table headers', async () => {
    const wrapper = mount(course_Clarifications, {
      propsData: {
        isAdmin: true,
        clarifications: [] as types.Clarification[],
        pagerItems: [
          {
            label: '«',
            class: 'disabled',
            page: 0,
          },
          {
            label: '1',
            class: 'active',
            page: 1,
          },
          {
            label: '»',
            class: 'disabled',
            page: 0,
          },
        ],
        pageSize: 10,
        page: 1,
      },
    });

    expect(wrapper.text()).toContain(T.wordsHomework);
    expect(wrapper.text()).toContain(T.wordsProblem);
  });
});
