import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';
import T from '../../lang';
import { omegaup } from '../../omegaup';

import course_StudentProgress from './StudentProgress.vue';

describe('StudentProgress.vue', () => {
  it('Should handle scores', async () => {
    const expectedDate = new Date('1/1/2020, 12:00:00 AM');
    const wrapper = shallowMount(course_StudentProgress, {
      propsData: {
        course: {
          alias: 'hello',
        },
        assignment: [
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
        student: {
          name: 'student',
          progress: [
            {
              Assignment: {
                problem: 44,
                problem2: 55,
              },
            },
          ],
          username: 'student',
        },
      },
    });
    expect(wrapper.find('div.bg-red'));
  });
});
