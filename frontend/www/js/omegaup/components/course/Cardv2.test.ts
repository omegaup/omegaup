import { shallowMount, mount } from '@vue/test-utils';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';

import course_Card from './Cardv2.vue';

describe('Card.vue', () => {
  const publicCourse: types.CourseCardPublic = {
    alias: 'test-course',
    lessonCount: 2,
    level: undefined,
    name: 'Test course',
    school_name: 'Test course school',
    studentCount: 2000,
  };

  const enrolledCourse: types.CourseCardEnrolled = {
    alias: 'test-enrolled-course',
    name: 'Enrolled course name',
    progress: 25,
    school_name: 'Test course school',
  };

  it('Should render information for public course', () => {
    const wrapper = shallowMount(course_Card, {
      propsData: {
        course: publicCourse,
        type: 'public',
      },
    });

    expect(wrapper.text()).toContain(publicCourse.name);
    expect(wrapper.text()).toContain(publicCourse.school_name);
    expect(wrapper.text()).toContain(
      ui.formatString(T.publicCourseCardMetrics, {
        lessonCount: publicCourse.lessonCount,
        studentCount: '2.0k',
      }),
    );
  });

  it('Should render information for enrolled course', () => {
    const wrapper = mount(course_Card, {
      propsData: {
        course: enrolledCourse,
        type: 'enrolled',
      },
    });

    expect(wrapper.text()).toContain(enrolledCourse.name);
    expect(wrapper.text()).toContain(enrolledCourse.school_name);
  });
});
