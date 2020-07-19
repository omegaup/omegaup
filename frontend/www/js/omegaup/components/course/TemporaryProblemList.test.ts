import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';

import course_TemporaryProblemLists from './TemporaryProblemList.vue';

describe('TemporaryProblemLists.vue', () => {
  it('Should handle empty assignments and problems', () => {
    const wrapper = shallowMount(course_TemporaryProblemLists, {
      propsData: {
        assignmentProblems: <types.ProblemsetProblem[]>[],
        selectedAssignment: {
          problemset_id: 0,
          alias: '',
          description: '',
          name: '',
          has_runs: false,
          max_points: 0,
          start_time: new Date(),
          finish_time: new Date(),
          order: 1,
          problems: [],
          scoreboard_url: '',
          scoreboard_url_admin: '',
          assignment_type: 'homework',
        },
        taggedProblems: <omegaup.Problem[]>[],
      },
    });

    expect(wrapper.text()).toContain(T.courseAddProblemsAddAssignmentDesc);
    expect(
      wrapper.find('.omegaup-course-problemlist .card-body').text(),
    ).toContain(T.courseAssignmentProblemsEmpty);
  });
});
