jest.mock('../../../../third_party/js/diff_match_patch.js');

import { types } from '../../api_types';
import { shallowMount, mount } from '@vue/test-utils';
import T from '../../lang';
import * as ui from '../../ui';
import course_Tabs from './Tabs.vue';

describe('Tabs.vue', () => {
  const courses: {
    enrolled: types.CourseCardEnrolled[];
    public: types.CourseCardPublic[];
    finished: types.CourseCardFinished[];
  } = {
    enrolled: [
      {
        alias: 'test-enrolled-course',
        name: 'Enrolled course name',
        progress: 25,
        school_name: 'Test course school',
      },
    ],
    public: [
      {
        alias: 'test-course',
        lessonCount: 2,
        level: 'introductory',
        name: 'Test course',
        school_name: 'Test course school',
        studentCount: 2000,
        alreadyStarted: false,
      },
    ],
    finished: [
      {
        alias: 'test-finished-course-A',
        name: 'Finished course name A',
      },
      {
        alias: 'test-finished-course-B',
        name: 'Finished course name B',
      },
    ],
  };

  it('Should show tabs', () => {
    const wrapper = shallowMount(course_Tabs, {
      propsData: {
        courses,
      },
    });
    expect(wrapper.text()).toContain(T.courseTabEnrolledUnlogged);
    expect(wrapper.text()).toContain(T.courseTabFinishedUnlogged);
    expect(wrapper.text()).toContain(T.courseTabPublic);
    expect(wrapper.text()).not.toContain(T.wordsStart);
    expect(wrapper.text()).toContain(T.courseCardMustLogIn);
  });

  it('Should show tabs with counts for logged user', () => {
    const wrapper = mount(course_Tabs, {
      propsData: {
        courses,
        loggedIn: true,
      },
    });
    expect(wrapper.text()).toContain(
      ui.formatString(T.courseTabEnrolled, {
        course_count: courses.enrolled.length,
      }),
    );
    expect(wrapper.text()).toContain(
      ui.formatString(T.courseTabFinished, {
        course_count: courses.finished.length,
      }),
    );
    expect(wrapper.text()).toContain(T.courseTabPublic);
    expect(wrapper.text()).toContain(T.wordsStart);
    expect(wrapper.text()).not.toContain(T.courseCardMustLogIn);
  });

  it('Should show the correct course cards', async () => {
    const wrapper = mount(course_Tabs, {
      propsData: {
        courses,
        loggedIn: true,
      },
    });

    // The public course should be visible
    expect(wrapper.text()).toContain(courses.public[0].name);

    // The enrolled course should be visible
    await wrapper.find('a[href="#enrolled"]').trigger('click');
    expect(wrapper.text()).toContain(courses.enrolled[0].name);

    // The finished courses should be visible
    await wrapper.find('a[href="#finished"]').trigger('click');
    expect(wrapper.text()).toContain(courses.finished[0].name);
    expect(wrapper.text()).toContain(courses.finished[1].name);

    // Should only show finished course B
    await wrapper.find('input.form-control').setValue(courses.finished[1].name);
    expect(wrapper.text()).not.toContain(courses.finished[0].name);
    expect(wrapper.text()).toContain(courses.finished[1].name);
  });
});
