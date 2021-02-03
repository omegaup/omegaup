import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import type { types } from '../../api_types';

import course_ProblemLists from './ProblemList.vue';

describe('ProblemLists.vue', () => {
  it('Should handle empty assignments and problems', () => {
    const wrapper = shallowMount(course_ProblemLists, {
      propsData: {
        assignmentProblems: [] as types.ProblemsetProblem[],
        assignments: [] as omegaup.Assignment[],
        selectedAssignment: {} as omegaup.Assignment,
        taggedProblems: [] as omegaup.Problem[],
        assignmentFormMode: omegaup.AssignmentFormMode.New,
      },
    });

    expect(wrapper.text()).toContain(T.courseAddProblemsEditAssignmentDesc);
    expect(
      wrapper.find('[data-course-problemlist] .card-body').text(),
    ).toContain(T.courseAssignmentProblemsEmpty);
  });
});
