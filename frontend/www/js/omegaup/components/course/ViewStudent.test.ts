import { mount, shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import type { types } from '../../api_types';

import course_ViewStudent from './ViewStudent.vue';

describe('ViewStudent.vue', () => {
  it('Should handle empty runs', () => {
    const wrapper = shallowMount(course_ViewStudent, {
      propsData: {
        course: {
          alias: 'hello',
        },
        problems: [],
      },
    });

    expect(wrapper.text()).toContain(T.courseStudentSelectStudent);
    expect(wrapper.text()).toContain(T.courseStudentSelectAssignment);
  });

  it('Should handle runs', async () => {
    const expectedDate = new Date('1/1/2020, 12:00:00 AM');
    const wrapper = mount(course_ViewStudent, {
      propsData: {
        course: {
          alias: 'hello',
        },
        assignments: [
          {
            alias: 'assignment',
            assignment_type: 'homework',
            description: 'Assignment description',
            start_time: new Date(0),
            finish_time: new Date(),
            name: 'Assignment',
            order: 1,
            scoreboard_url: '',
            scoreboard_url_admin: '',
          } as omegaup.Assignment,
        ],
        problems: [
          {
            alias: 'problem',
            commit: '',
            letter: 'A',
            order: 1,
            points: 1,
            runs: [
              {
                penalty: 0,
                score: 1,
                source: 'print(3)',
                time: expectedDate,
                verdict: 'AC',
              } as omegaup.CourseProblemRun,
            ],
            submissions: 1,
            title: 'problem_1',
            visits: 1,
          } as types.CourseProblem,
        ],
        students: [
          {
            name: 'student',
            username: 'student',
            progress: {
              problem: 1,
            },
          } as types.CourseStudent,
        ],
        initialStudent: {
          name: 'student',
          username: 'student',
          progress: {
            problem: 1,
          },
        } as types.CourseStudent,
      },
    });

    const assignments = wrapper.find('select[data-assignment]')
      .element as HTMLInputElement;
    assignments.value = 'assignment';
    await assignments.dispatchEvent(new Event('change'));
    expect(wrapper.find('div[data-markdown-statement]').text()).toBe(
      'Assignment description',
    );
    await wrapper.find('a[data-problem-alias="problem"]').trigger('click');

    expect(wrapper.find('table tbody td').text()).toBe(
      expectedDate.toLocaleString(T.locale),
    );
  });
});
