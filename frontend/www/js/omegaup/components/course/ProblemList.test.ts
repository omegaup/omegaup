import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';

import course_ProblemLists from './ProblemList.vue';

describe('ProblemLists.vue', () => {
  it('Should handle empty assignments and problems', () => {
    const wrapper = shallowMount(course_ProblemLists, {
      propsData: {
        assignmentProblems: <types.ProblemsetProblem[]>[],
        assignments: <omegaup.Assignment[]>[],
        selectedAssignment: <omegaup.Assignment>{},
        taggedProblems: <omegaup.Problem[]>[],
      },
    });

    expect(wrapper.find('select[name="assignments"]').text()).toBe('');
    expect(
      wrapper.find('.omegaup-course-problemlist button.form-control').text(),
    ).toBe(T.courseEditAddProblems);
  });

  it('Should handle assignments and problems', () => {
    const wrapper = shallowMount(course_ProblemLists, {
      propsData: {
        assignmentProblems: <types.ProblemsetProblem[]>[],
        assignments: <omegaup.Assignment[]>[
          {
            alias: 'PE',
            assignment_type: 'test',
            description: 'Primer examen',
            finish_time: new Date(),
            has_runs: false,
            max_points: 900,
            name: 'Primer examen',
            order: 0,
            publish_time_delay: 0,
            scoreboard_url: 'sb01',
            scoreboard_url_admin: 'sb02',
            start_time: new Date(),
          },
          {
            alias: 'SE',
            assignment_type: 'test',
            description: 'Segundo examen',
            finish_time: new Date(),
            has_runs: false,
            max_points: 900,
            name: 'Segundo examen',
            order: 0,
            publish_time_delay: 0,
            scoreboard_url: 'sb03',
            scoreboard_url_admin: 'sb04',
            start_time: new Date(),
          },
        ],
        selectedAssignment: <omegaup.Assignment>{
          alias: 'SE',
          assignment_type: 'test',
          description: 'Segundo examen',
          finish_time: new Date(),
          has_runs: false,
          max_points: 900,
          name: 'Segundo examen',
          order: 0,
          publish_time_delay: 0,
          scoreboard_url: 'sb03',
          scoreboard_url_admin: 'sb04',
          start_time: new Date(),
        },
        taggedProblems: <omegaup.Problem[]>[],
      },
    });

    expect(wrapper.find('select[name="assignments"]').text()).toContain(
      'Primer examen',
    );
    expect(wrapper.find('select[name="assignments"]').text()).toContain(
      'Segundo examen',
    );
  });
});
