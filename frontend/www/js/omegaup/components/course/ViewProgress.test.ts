import { shallowMount } from '@vue/test-utils';
import T from '../../lang';
import course_ViewProgress, { escapeXml, toOds } from './ViewProgress.vue';
import { Percentage } from '../../csv';
import type { types } from '../../api_types';
import { formatString } from '../../ui';

describe('ViewProgress.vue', () => {
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
        extraPoints: 0,
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
  const courseName = baseViewProgressProps.course.name;

  it('Should handle scores', async () => {
    const wrapper = shallowMount(course_ViewProgress, {
      propsData: baseViewProgressProps,
    });
    expect(wrapper.text()).toContain(
      T.courseStudentsProgressExportToSpreadsheet,
    );
  });

  it('Should handle escaped xml cells', () => {
    const escapedXml = escapeXml('Escaped <text>');
    expect(escapedXml).toBe('Escaped &lt;text&gt;');
  });

  it('Should handle ods content', () => {
    const wrapper = shallowMount(course_ViewProgress, {
      propsData: baseViewProgressProps,
    });
    const odsContent = toOds(courseName, wrapper.vm.progressTable);
    expect(odsContent).toBe(`<table:table table:name="${courseName}">
<table:table-column table:number-columns-repeated="4"/>
<table:table-row>
<table:table-cell office:value-type="string"><text:p>${
      T.profileUsername
    }</text:p>\
</table:table-cell><table:table-cell office:value-type="string"><text:p>${
      T.wordsName
    }\
</text:p></table:table-cell><table:table-cell office:value-type="string"><text:p>\
${T.courseProgressGlobalScore}</text:p></table:table-cell><table:table-cell \
office:value-type="string"><text:p>${assignment.name} ${formatString(
      T.studentProgressPoints,
      {
        points: assignment.points,
      },
    )}</text:p></table:table-cell>\
</table:table-row>
<table:table-row>
<table:table-cell office:value-type="string"><text:p>${
      student.username
    }</text:p>\
</table:table-cell><table:table-cell office:value-type="string"><text:p>${
      student.name
    }\
</text:p></table:table-cell><table:table-cell office:value-type="percentage" \
office:value="${new Percentage(student.courseProgress / 100).value.toFixed(
      4,
    )}"><text:p>${new Percentage(
      student.courseProgress / 100,
    ).toString()}</text:p>\
</table:table-cell><table:table-cell office:value-type="percentage" office:value="\
${new Percentage(
  student.assignments[assignment.alias].progress / 100,
).value.toFixed(4)}"><text:p>${new Percentage(
      student.assignments[assignment.alias].progress / 100,
    ).toString()}</text:p></table:table-cell></table:table-row>
</table:table>`);
  });
});
