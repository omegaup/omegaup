import { mount, shallowMount } from '@vue/test-utils';

import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';
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

  const submissionFeedback: types.SubmissionFeedback = {
    author: 'omegaUp',
    author_classname: 'user-rank-unranked',
    date: new Date(),
    feedback: 'Test feedback',
    feedback_thread: [],
    submission_feedback_id: 1,
  };

  const assignment_a: omegaup.Assignment = {
    alias: 'assignment_a',
    assignment_type: 'homework',
    description: 'Assignment description A',
    start_time: new Date(0),
    finish_time: new Date(),
    name: 'Assignment',
    order: 1,
    scoreboard_url: '',
    scoreboard_url_admin: '',
  };

  const assignment_b: omegaup.Assignment = {
    alias: 'assignment_b',
    assignment_type: 'homework',
    description: 'Assignment description B',
    start_time: new Date(0),
    finish_time: new Date(),
    name: 'Assignment',
    order: 1,
    scoreboard_url: '',
    scoreboard_url_admin: '',
  };

  const student_a: types.CourseStudent = {
    name: 'student_a',
    username: 'student_a',
  };

  const student_b: types.CourseStudent = {
    name: 'student_b',
    username: 'student_b',
  };

  const expectedDate = new Date('1/1/2020, 12:00:00 AM');

  const run: types.CourseRun = {
    guid: 'guid-1',
    language: 'cpp',
    memory: 200,
    penalty: 0,
    score: 1,
    status: 'ready',
    runtime: 1,
    submit_delay: 1,
    source: 'print(3)',
    time: expectedDate,
    verdict: 'AC',
  };

  const problem: types.CourseProblem = {
    accepted: 1,
    alias: 'problem_a',
    commit: '',
    letter: 'A',
    order: 1,
    points: 1,
    runs: [
      run,
      { ...run, ...{ guid: 'guid-2', feedback: submissionFeedback } },
    ],
    submissions: 1,
    title: 'problem_1',
    visits: 1,
    difficulty: 1,
    languages: 'cpp',
    visibility: 1,
    version: 'abcdef',
  };

  it('Should handle runs', async () => {
    const wrapper = mount(course_ViewStudent, {
      propsData: {
        course: {
          alias: 'hello',
        },
        assignments: [assignment_a, assignment_b],
        problems: [
          problem,
          { ...problem, ...{ alias: 'problem_b', title: 'problem_2' } },
        ],
        students: [student_a, student_b],
        student: student_a,
      },
    });

    const students = wrapper.find('select[data-student]')
      .element as HTMLInputElement;
    students.value = 'student_b';
    await students.dispatchEvent(new Event('change'));
    expect(wrapper.emitted('update')).toEqual([
      [{ student: student_b.username, assignmentAlias: null }],
    ]);
    expect(
      (wrapper.find('select[data-student]').element as HTMLInputElement).value,
    ).toBe('student_b');

    const assignments = wrapper.find('select[data-assignment]')
      .element as HTMLInputElement;
    assignments.value = 'assignment_b';
    await assignments.dispatchEvent(new Event('change'));
    expect(wrapper.find('div[data-markdown-statement]').text()).toBe(
      'Assignment description B',
    );

    assignments.value = 'assignment_a';
    await assignments.dispatchEvent(new Event('change'));
    expect(wrapper.find('div[data-markdown-statement]').text()).toBe(
      'Assignment description A',
    );

    await wrapper.find('a[data-problem-alias="problem_a"]').trigger('click');

    expect(wrapper.find('table tbody td').text()).toBe(
      expectedDate.toLocaleString(T.locale),
    );

    await wrapper.find('a[data-problem-alias="problem_b"]').trigger('click');

    expect(wrapper.find('table tbody td').text()).toBe(
      expectedDate.toLocaleString(T.locale),
    );

    await wrapper.find('tr[data-run-guid="guid-1"]').trigger('click');
    expect(wrapper.text()).toContain(T.feedbackNotSentYet);

    await wrapper.find('tr[data-run-guid="guid-2"]').trigger('click');
    expect(wrapper.text()).toContain(submissionFeedback.feedback);
    expect(wrapper.text()).toContain(
      ui.formatString(T.feedbackLeftBy, {
        date: time.formatDate(submissionFeedback.date),
      }),
    );
  });
});
