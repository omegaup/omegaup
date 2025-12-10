import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';

import course_CardPublic from './CardPublic.vue';

describe('CardPublic.vue', () => {
  const publicCourse: types.CourseCardPublic = {
    alias: 'test-course',
    lessonCount: 2,
    level: 'introductory',
    name: 'Test course',
    school_name: 'Test course school',
    studentCount: 2000,
    alreadyStarted: false,
  };

  it('Should render information for public course', () => {
    const wrapper = shallowMount(course_CardPublic, {
      propsData: {
        course: publicCourse,
      },
    });

    expect(wrapper.text()).toContain(publicCourse.name);
    expect(wrapper.text()).toContain(publicCourse.school_name);
    expect(wrapper.text()).toContain(
      ui.formatString(T.publicCourseCardMetricsStudents, {
        studentCount: '2.0k',
      }),
    );
    expect(wrapper.text()).toContain(T.courseCardPublicLevelIntroductory);
  });

  it('Should click on the course name even when a user is not logged in', async () => {
    const wrapper = shallowMount(course_CardPublic, {
      propsData: {
        course: publicCourse,
      },
    });

    expect(
      wrapper.find(`a[href="/course/${publicCourse.alias}/"]`).text(),
    ).toBe(publicCourse.name);

    // Now, with the user logged in, the result should remain the same.
    await wrapper.setProps({ loggedIn: true });

    expect(
      wrapper.find(`a[href="/course/${publicCourse.alias}/"]`).text(),
    ).toBe(publicCourse.name);
  });
});
