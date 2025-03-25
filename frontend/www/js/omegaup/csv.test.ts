import { shallowMount } from '@vue/test-utils';
import T from './lang';
import course_ViewProgress from './components/course/ViewProgress.vue';
import type { types } from './api_types';
import { escapeCsv, toCsv, Percentage } from './csv';
import { formatString } from './ui';

describe('csv_utils', () => {
  if (typeof window.URL.createObjectURL === 'undefined') {
    Object.defineProperty(window.URL, 'createObjectURL', {
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      value: (obj: any) => '',
      writable: true,
    });
  }
  const baseViewProgressProps = {
    course: {
      alias: 'hello',
      name: 'Hello course',
    } as types.CourseDetails,
    assignmentsProblems: [
      {
        alias: 'test-assignment-a',
        name: 'Test assignment A',
        points: 200,
        problems: [
          {
            alias: 'test-problem-a',
            title: 'Test problem A',
            isExtraProblem: false,
            points: 100,
            order: 1,
          },
          {
            alias: 'test-problem-b',
            title: 'Test problem B',
            isExtraProblem: false,
            points: 100,
            order: 2,
          },
        ],
      },
    ] as types.AssignmentsProblemsPoints[],
    students: [
      {
        username: 'test_user',
        name: '',
        country_id: '',
        classname: 'user-rank-unranked',
        courseScore: 100,
        courseProgress: 50,
        assignments: {
          'test-assignment-a': {
            score: 90,
            progress: 50,
            problems: {
              'test-problem-a': {
                score: 90,
                progress: 90,
              },
              'test-problem-b': {
                score: 1,
                progress: 1,
              },
            },
          },
        },
      },
    ] as types.StudentProgressInCourse[],
    completeStudentsProgress: [
      {
        username: 'test_user',
        name: '',
        country_id: '',
        classname: 'user-rank-unranked',
        courseScore: 100,
        courseProgress: 50,
        assignments: {
          'test-assignment-a': {
            score: 90,
            progress: 50,
            problems: {
              'test-problem-a': {
                score: 90,
                progress: 90,
              },
              'test-problem-b': {
                score: 1,
                progress: 1,
              },
            },
          },
        },
      },
    ] as types.StudentProgressInCourse[],
    pagerItems: [
      {
        class: 'disabled',
        label: '«',
        page: 0,
      },
    ] as types.PageItem[],
    totalRows: 1,
    page: 1,
    length: 1,
  };

  const student = baseViewProgressProps.students[0];
  const assignment = baseViewProgressProps.assignmentsProblems[0];

  it('Should handle escaped csv cells', () => {
    const escapedCell = escapeCsv('Escaped "text"');
    expect(escapedCell).toBe('"Escaped ""text""');
  });

  it('Should handle csv content', () => {
    const wrapper = shallowMount(course_ViewProgress, {
      propsData: baseViewProgressProps,
    });
    const globalScore = `${student.courseProgress.toFixed(2)}%`;

    const csvContent = toCsv(wrapper.vm.progressTable);
    expect(csvContent).toBe(`${T.profileUsername},${T.wordsName},${
      T.courseProgressGlobalScore
    },${assignment.name} ${formatString(T.studentProgressPoints, {
      points: assignment.points,
    })}\r
${student.username},${student.name},${globalScore},${new Percentage(
      student.assignments[assignment.alias].progress / 100,
    ).toString()}`);
  });
});
