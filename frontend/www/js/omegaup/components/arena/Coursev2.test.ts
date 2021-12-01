import { shallowMount } from '@vue/test-utils';
import arena_Course from './Coursev2.vue';
import { types } from '../../api_types';

describe('Coursev2.vue', () => {
  const course: types.ArenaCourseDetails = {
    alias: 'test-course',
    name: 'Test Course',
  };
  const assignment: types.ArenaCourseAssignment = {
    alias: 'test-assignment',
    name: 'Test Assignment',
    description: 'Test assignment description',
  };
  const problems: types.ArenaCourseProblem[] = [
    {
      alias: 'test-problem-1',
      title: 'Test Problem 1',
      letter: 'A',
    },
    {
      alias: 'test-problem-2',
      title: 'Test Problem 2',
      letter: 'B',
    },
  ];
  const currentProblem: types.ArenaCourseCurrentProblem = {
    alias: 'test-problem-1',
    title: 'Test Problem 1',
  };

  it('Should show the course summary', () => {
    const wrapper = shallowMount(arena_Course, {
      propsData: {
        course,
        assignment,
        problems,
        currentProblem: null,
      },
    });

    expect(wrapper.text()).toContain(course.name);
    expect(wrapper.text()).toContain(assignment.name);
    expect(wrapper.text()).toContain(course.name);
    expect(wrapper.text()).toContain(problems[0].title);
    expect(wrapper.text()).toContain(problems[1].title);
  });
});
