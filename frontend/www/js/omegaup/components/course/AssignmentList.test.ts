import { createLocalVue, shallowMount } from '@vue/test-utils';
import Sortable from 'sortablejs';

import T from '../../lang';
import { omegaup } from '../../omegaup';
import type { types } from '../../api_types';

import course_AssignmentList from './AssignmentList.vue';

describe('AssignmentList.vue', () => {
  it('Should handle empty content list', () => {
    const wrapper = shallowMount(course_AssignmentList, {
      propsData: {
        content: [] as types.CourseAssignment[],
        courseAlias: 'course_alias',
      },
    });

    expect(wrapper.text()).toContain(T.courseContentEmpty);
  });

  const localVue = createLocalVue();
  localVue.directive('Sortable', {
    inserted: (el: HTMLElement, binding) => {
      new Sortable(el, binding.value || {});
    },
  });

  it('Should handle content list', async () => {
    const wrapper = shallowMount(course_AssignmentList, {
      localVue,
      propsData: {
        content: [
          {
            alias: 'CA',
            assignment_type: 'test',
            description: 'First test',
            finish_time: new Date(),
            has_runs: false,
            max_points: 900,
            name: 'Firste test',
            order: 0,
            publish_time_delay: 0,
            scoreboard_url: 'cb01',
            scoreboard_url_admin: 'sb02',
            start_time: new Date(),
          },
        ] as omegaup.Assignment[],
        courseAlias: 'course_alias',
        assignmentFormMode: omegaup.AssignmentFormMode.Default,
      },
    });
    await wrapper
      .find('.omegaup-course-assignmentlist button[type="submit"]')
      .trigger('click');

    expect(wrapper.text()).not.toContain(T.courseExamEmpty);
  });
});
