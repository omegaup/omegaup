import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

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

    expect(wrapper.text()).toBe(T.courseAssignmentProblemRunsEmpty);
  });

  it('Should handle runs', async () => {
    const expectedDate = '1/1/2020, 12:00:00 AM';
    const wrapper = shallowMount(course_ViewStudent, {
      propsData: {
        course: {
          alias: 'hello',
        },
        assignment: [
          {
            alias: 'assignment',
            assignment_type: 'homework',
            description: 'Assignment',
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
                time: new Date(expectedDate),
                verdict: 'AC',
              } as omegaup.CourseProblemRun,
            ],
            submissions: 1,
            visits: 1,
          } as omegaup.CourseProblem,
        ],
        students: [
          {
            name: 'student',
            username: 'student',
            progress: {
              problem: 1,
            },
          } as omegaup.CourseStudent,
        ],
        initialStudent: {
          name: 'student',
          username: 'student',
          progress: {
            problem: 1,
          },
        } as omegaup.CourseStudent,
      },
    });
    await wrapper.find('a[data-problem-alias="problem"]').trigger('click');

    expect(wrapper.find('table tbody td').text()).toBe(expectedDate);
  });
});
