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
        assignmentFormMode: omegaup.AssignmentFormMode.Default,
      },
    });

    const emptyState = wrapper.findComponent({
      name: 'EmptyState',
    });

    expect(emptyState.exists()).toBe(true);
    expect(emptyState.props('title')).toBe(T.courseContentEmptyTitle);
    expect(emptyState.props('description')).toBe(
      T.courseContentEmptyDescription,
    );
    expect(emptyState.props('buttonText')).toBe(T.courseAddContent);
  });

  it('Should emit emit-new when empty-state action is clicked', async () => {
    const wrapper = shallowMount(course_AssignmentList, {
      propsData: {
        content: [] as types.CourseAssignment[],
        courseAlias: 'course_alias',
        assignmentFormMode: omegaup.AssignmentFormMode.Default,
      },
    });

    await wrapper.find('omegaup-common-empty-state-stub').vm.$emit('action');

    expect(wrapper.emitted('emit-new')).toBeTruthy();
  });

  it('Should hide footer when content is empty and show it when content exists', async () => {
    const emptyWrapper = shallowMount(course_AssignmentList, {
      propsData: {
        content: [] as types.CourseAssignment[],
        courseAlias: 'course_alias',
        assignmentFormMode: omegaup.AssignmentFormMode.Default,
      },
    });

    const emptyFooter = emptyWrapper.find('.card-footer')
      .element as HTMLElement;
    expect(emptyFooter).toBeTruthy();
    expect(emptyFooter).toHaveStyle({ display: 'none' });

    const populatedWrapper = shallowMount(course_AssignmentList, {
      propsData: {
        content: [
          {
            alias: 'CA',
            assignment_type: 'test',
            description: 'First test',
            finish_time: new Date(),
            has_runs: false,
            max_points: 900,
            name: 'First test',
            order: 0,
            publish_time_delay: 0,
            scoreboard_url: 'cb01',
            scoreboard_url_admin: 'sb02',
            start_time: new Date(),
          },
        ] as types.CourseAssignment[],
        courseAlias: 'course_alias',
        assignmentFormMode: omegaup.AssignmentFormMode.Default,
      },
    });

    const populatedFooter = populatedWrapper.find('.card-footer')
      .element as HTMLElement;
    expect(populatedFooter).not.toHaveStyle({ display: 'none' });
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
