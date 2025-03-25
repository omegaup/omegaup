import { mount } from '@vue/test-utils';
import { types } from '../../api_types';

import course_CardFinished from './CardFinished.vue';

describe('CardFinished.vue', () => {
  const finishedCourse: types.CourseCardFinished = {
    alias: 'test-finished-course',
    name: 'Finished course name',
  };

  it('Should render information for finished course', () => {
    const wrapper = mount(course_CardFinished, {
      propsData: {
        course: finishedCourse,
      },
    });

    expect(wrapper.text()).toContain(finishedCourse.name);
    expect(wrapper.text()).toContain('⭐');
  });
});
