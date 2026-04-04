import { mount } from '@vue/test-utils';
import { types } from '../../api_types';

import course_CardEnrolled from './CardEnrolled.vue';

describe('CardEnrolled.vue', () => {
  const enrolledCourse: types.CourseCardEnrolled = {
    alias: 'test-enrolled-course',
    name: 'Enrolled course name',
    progress: 25,
    school_name: 'Test course school',
  };

  it('Should render information for enrolled course', () => {
    const wrapper = mount(course_CardEnrolled, {
      propsData: {
        course: enrolledCourse,
      },
    });

    expect(wrapper.text()).toContain(enrolledCourse.name);
    expect(wrapper.text()).toContain(enrolledCourse.school_name);
  });
});
