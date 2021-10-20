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

  const submissionFeedback = {
    author: 'omegaUp',
    author_classname: 'user-rank-unranked',
    date: new Date(),
    feedback: 'Test feedback',
  } as types.SubmissionFeedback;

  const assignment = {
    alias: 'assignment',
    assignment_type: 'homework',
    description: 'Assignment description',
    start_time: new Date(0),
    finish_time: new Date(),
    name: 'Assignment',
    order: 1,
    scoreboard_url: '',
    scoreboard_url_admin: '',
  } as omegaup.Assignment;

  const student = {
    name: 'student',
    username: 'student',
    progress: {
      problem: 1,
    },
  } as types.CourseStudent;

  it('Should handle runs', async () => {
    const expectedDate = new Date('1/1/2020, 12:00:00 AM');
    const wrapper = mount(course_ViewStudent, {
      propsData: {
        course: {
          alias: 'hello',
        },
        assignments: [assignment],
        problems: [
          {
            accepted: 1,
            alias: 'problem',
            commit: '',
            letter: 'A',
            order: 1,
            points: 1,
            runs: [
              {
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
              } as types.CourseRun,
              {
                guid: 'guid-2',
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
                feedback: submissionFeedback,
              } as types.CourseRun,
            ],
            submissions: 1,
            title: 'problem_1',
            visits: 1,
          } as types.CourseProblem,
        ],
        students: [student],
        student: student,
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

    await wrapper.find('tr[data-run-guid="guid-1"]').trigger('click');
    expect(wrapper.text()).toContain(T.feedbackNotSentYet);

    await wrapper.find('a[data-show-feedback-form]').trigger('click');
    expect(wrapper.find('button[data-feedback-button]').element).toBeDisabled();

    await wrapper.find('textarea').setValue(submissionFeedback.feedback);
    expect(wrapper.find('button[data-feedback-button]').element).toBeEnabled();
    wrapper.find('button[data-feedback-button]').trigger('click');
    expect(wrapper.emitted('set-feedback')).toBeDefined();
    expect(wrapper.emitted('set-feedback')).toEqual([
      [
        {
          guid: 'guid-1',
          feedback: submissionFeedback.feedback,
          isUpdate: false,
          assignmentAlias: assignment.alias,
          studentUsername: student.username,
        },
      ],
    ]);

    await wrapper.find('tr[data-run-guid="guid-2"]').trigger('click');
    expect(wrapper.text()).toContain(submissionFeedback.feedback);
    expect(wrapper.text()).toContain(
      ui.formatString(T.feedbackLeftBy, {
        date: time.formatDate(submissionFeedback.date),
      }),
    );
  });
});
