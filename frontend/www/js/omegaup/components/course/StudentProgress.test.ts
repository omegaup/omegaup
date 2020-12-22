import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import { omegaup } from '../../omegaup';

import course_StudentProgress from './StudentProgress.vue';
import { types } from '../../api_types';

describe('StudentProgress.vue', () => {
  it('Should handle scores', async () => {
    const wrapper = shallowMount(course_StudentProgress, {
      propsData: {
        course: {
          alias: 'hello',
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
        student: <types.StudentProgress>{
          name: 'student',
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
        },
        problemTitles: {
          problem1: 'Problem 1',
          problem2: 'Problem 2',
        },
      },
    });
    expect(wrapper.find('div.bg-yellow').exists()).toBe(true);
    expect(wrapper.find('div.bg-red').exists()).toBe(true);
    expect(wrapper.find('td[data-global-score]').text()).toBe('50%');
  });
});
