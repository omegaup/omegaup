import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import T from '../../lang';
import { omegaup } from '../../omegaup';

import course_ViewProgress, {
  escapeCsv,
  escapeXml,
  toOds,
  toCsv,
} from './ViewProgress.vue';
import { types } from '../../api_types';

describe('ViewProgress.vue', () => {
  if (typeof window.URL.createObjectURL === 'undefined') {
    Object.defineProperty(window.URL, 'createObjectURL', {
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      value: (obj: any) => '',
      writable: true,
    });
  }
  const baseViewProgressProps = {
    course: <types.CourseDetails>{
      alias: 'hello',
      name: 'Hello course',
    },
    assignments: <omegaup.Assignment[]>[
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
    ],
    students: <types.StudentProgress[]>[
      {
        name: 'student',
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
    ],
  };
  const student = baseViewProgressProps.students[0];
  const assignment = baseViewProgressProps.assignments[0];
  const score = Object.values(student.score[assignment.alias]).reduce(
    (accumulator: number, currentValue: number) => accumulator + currentValue,
    0,
  );
  const courseName = baseViewProgressProps.course.name;

  it('Should handle scores', async () => {
    const wrapper = shallowMount(course_ViewProgress, {
      propsData: baseViewProgressProps,
    });
    expect(wrapper.text()).toContain(
      T.courseStudentsProgressExportToSpreadsheet,
    );
  });

  it('Should handle escaped csv cells', () => {
    const escapedCell = escapeCsv('Escaped "text"');
    expect(escapedCell).toBe('"Escaped ""text""');
  });

  it('Should handle escaped xml cells', () => {
    const escapedXml = escapeXml('Escaped <text>');
    expect(escapedXml).toBe('Escaped &lt;text&gt;');
  });

  it('Should handle ods content', () => {
    const wrapper = shallowMount(course_ViewProgress, {
      propsData: baseViewProgressProps,
    });
    const globalScore = wrapper.vm.getGlobalScoreByStudent(student);

    const odsContent = toOds(courseName, wrapper.vm.progressTable);
    expect(odsContent).toBe(`<table:table table:name="${courseName}">
<table:table-column table:number-columns-repeated="4"/>
<table:table-row>
<table:table-cell office:value-type="string"><text:p>${T.profileUsername}\
</text:p></table:table-cell><table:table-cell office:value-type="string">\
<text:p>${T.wordsName}</text:p></table:table-cell><table:table-cell \
office:value-type="string"><text:p>${assignment.name}</text:p>\
</table:table-cell><table:table-cell office:value-type="string"><text:p>\
${T.courseProgressGlobalScore}</text:p></table:table-cell></table:table-row>
<table:table-row>
<table:table-cell office:value-type="string"><text:p>${student.username}\
</text:p></table:table-cell><table:table-cell office:value-type="string">\
<text:p>${student.name}</text:p></table:table-cell><table:table-cell \
office:value-type="float" office:value="${score}"><text:p>${score}</text:p>\
</table:table-cell><table:table-cell office:value-type="string"><text:p>\
${globalScore}%</text:p></table:table-cell></table:table-row>
</table:table>`);
  });

  it('Should handle csv content', () => {
    const wrapper = shallowMount(course_ViewProgress, {
      propsData: baseViewProgressProps,
    });
    const globalScore = wrapper.vm.getGlobalScoreByStudent(student);

    const csvContent = toCsv(wrapper.vm.progressTable);
    expect(csvContent)
      .toBe(`${T.profileUsername},${T.wordsName},${assignment.name},${T.courseProgressGlobalScore}\r
${student.username},${student.name},${score},${globalScore}%`);
  });
});
