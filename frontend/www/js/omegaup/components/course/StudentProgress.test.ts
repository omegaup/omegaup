import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';
import T from '../../lang';
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
        assignment: <omegaup.Assignment[]>[
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
              ['problem2']: 44,
            },
          },
          score: {
            ['assignment']: {
              ['problem1']: 55,
              ['problem2']: 44,
            },
          },
          username: 'student',
        },
      },
    });
    expect(wrapper.find('div.bg-yellow'));
    expect(wrapper.find('div.bg-red'));
    expect(wrapper.find('div[title="55%"]'));
    expect(wrapper.find('div[title="44%"]'));
  });
});
