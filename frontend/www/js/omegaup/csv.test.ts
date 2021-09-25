import { shallowMount } from '@vue/test-utils';
import T from './lang';
import { omegaup } from './omegaup';

import course_ViewProgress from './components/course/ViewProgress.vue';
import type { types } from './api_types';
import { escapeCsv, toCsv } from './csv';

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
    assignments: [
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
        max_points: 200,
      } as omegaup.Assignment,
    ] as omegaup.Assignment[],
    students: [
      {
        name: 'student',
        classname: 'user-rank-unranked',
        points: {
          ['assignment']: { ['problem1']: 100, ['problem2']: 100 },
        },
        progress: {
          ['assignment']: { ['problem1']: 55, ['problem2']: 44 },
        },
        score: {
          ['assignment']: { ['problem1']: 55, ['problem2']: 44 },
        },
        username: 'student',
      } as types.StudentProgress,
    ] as types.StudentProgress[],
  };
  const student = baseViewProgressProps.students[0];
  const assignment = baseViewProgressProps.assignments[0];
  const score = Object.values(student.score[assignment.alias]).reduce(
    (accumulator: number, currentValue: number) => accumulator + currentValue,
    0,
  );

  it('Should handle escaped csv cells', () => {
    const escapedCell = escapeCsv('Escaped "text"');
    expect(escapedCell).toBe('"Escaped ""text""');
  });

  it('Should handle csv content', () => {
    const wrapper = shallowMount(course_ViewProgress, {
      propsData: baseViewProgressProps,
    });
    const globalScore = wrapper.vm.getGlobalScoreByStudent(student);

    const csvContent = toCsv(wrapper.vm.progressTable);
    expect(csvContent).toBe(`${T.profileUsername},${T.wordsName},${
      T.courseProgressGlobalScore
    },${assignment.name}\r
${student.username},${student.name},${globalScore},${score.toFixed(2)}`);
  });
});
