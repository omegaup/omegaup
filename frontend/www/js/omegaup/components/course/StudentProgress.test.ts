import { shallowMount } from '@vue/test-utils';
import course_StudentProgress from './StudentProgress.vue';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';

describe('StudentProgress.vue', () => {
  it('Should handle scores', async () => {
    const wrapper = shallowMount(course_StudentProgress, {
      propsData: {
        assignmentsProblems: [
          {
            alias: 'test-assignment-a',
            name: 'Test assignment A',
            points: 200,
            extraPoints: 100,
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
              {
                alias: 'test-problem-c',
                title: 'Test problem C',
                isExtraProblem: false,
                points: 100,
                order: 3,
              },
            ],
          },
          {
            alias: 'test-assignment-b',
            name: 'Test assignment B',
            points: 0,
            extraPoints: 0,
            problems: [],
          },
        ] as types.AssignmentsProblemsPoints[],
        courseAlias: 'course',
        studentProgress: {
          username: 'test_user',
          name: '',
          country_id: '',
          classname: 'user-rank-unranked',
          courseScore: 100,
          courseProgress: 50,
          assignments: {
            'test-assignment-a': {
              score: 100,
              progress: 50,
              problems: {
                'test-problem-a': {
                  score: 100,
                  progress: 100,
                },
                'test-problem-b': {
                  score: 100,
                  progress: 10,
                },
                'test-problem-c': {
                  score: 100,
                  progress: 60,
                },
              },
            },
          },
        } as types.StudentProgressInCourse,
      },
    });
    expect(wrapper.find('a.bg-green').exists()).toBe(true);
    expect(wrapper.find('a.bg-yellow').exists()).toBe(true);
    expect(wrapper.find('a.bg-red').exists()).toBe(true);
    expect(wrapper.find('td[data-global-score]').text()).toBe(
      '50% ' +
        ui.formatString(T.studentProgressPoints, {
          points: 100,
        }),
    );
  });
});
