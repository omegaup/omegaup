import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';

import course_Card from './Cardv2.vue';

describe('Card.vue', () => {
  const publicCourse: types.CourseCardPublic = {
    alias: 'test-course',
    lessonsCount: 2,
    level: undefined,
    name: 'Test course',
    school_name: 'Test course school',
    studentsCount: 2,
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
        nLessons: publicCourse.lessonsCount,
        nStudents: (publicCourse.studentsCount / 1000).toFixed(1),
      }),
    );
  });
});
