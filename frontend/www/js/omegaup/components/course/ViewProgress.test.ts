import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import T from '../../lang';
import { omegaup } from '../../omegaup';

import course_ViewProgress from './ViewProgress.vue';
import { types } from '../../api_types';

describe('ViewProgress.vue', () => {
  Object.defineProperty(window.URL, 'createObjectURL', {});
  it('Should handle scores', async () => {
    const wrapper = shallowMount(course_ViewProgress, {
      propsData: {
        course: <types.CourseDetails>{
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
          },
        ],
      },
    });
    expect(wrapper.text()).toContain(
      T.courseStudentsProgressExportToSpreadsheet,
    );
  });
});
