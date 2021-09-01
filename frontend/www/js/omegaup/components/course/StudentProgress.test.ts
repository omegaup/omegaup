import { shallowMount } from '@vue/test-utils';
import { omegaup } from '../../omegaup';

import course_StudentProgress from './StudentProgress.vue';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';

describe('StudentProgress.vue', () => {
  it('Should handle scores', async () => {
    const wrapper = shallowMount(course_StudentProgress, {
      propsData: {
        course: {
          alias: 'hello',
        },
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
        student: {
          name: 'student',
          classname: 'user-rank-unranked',
          points: {
            ['assignment']: {
              ['problem1']: 100,
              ['problem2']: 100,
            },
          },
          progress: {
            ['assignment']: {
              ['problem1']: 55,
              ['problem2']: 45,
            },
          },
          score: {
            ['assignment']: {
              ['problem1']: 55,
              ['problem2']: 45,
            },
          },
          username: 'student',
        } as types.StudentProgress,
        problemTitles: {
          problem1: 'Problem 1',
          problem2: 'Problem 2',
        },
      },
    });
    expect(wrapper.find('a.bg-yellow').exists()).toBe(true);
    expect(wrapper.find('a.bg-red').exists()).toBe(true);
    expect(wrapper.find('td[data-global-score]').text()).toBe(
      '50% ' +
        ui.formatString(T.studentProgressDescriptionTotalPoints, {
          points: 100,
        }),
    );
  });
});
