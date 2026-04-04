import { mount } from '@vue/test-utils';

import course_Clone from './Clone.vue';

describe('Clone.vue', () => {
  it('Should handle clone course view', () => {
    const wrapper = mount(course_Clone, {
      propsData: {
        initialAlias: 'CA',
        initialName: 'initial name',
      },
    });

    expect(
      wrapper.find('div[data-course-clone] input[type="date"]').text(),
    ).toBe('');
  });
});
