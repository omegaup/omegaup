import expect from 'expect';
import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';

import T from '../../lang';

import course_ViewStudent from './ViewStudent.vue';

describe('ViewStudent.vue', () => {
  it('empty runs', () => {
    const wrapper = shallowMount(course_ViewStudent, {
      propsData: {
        course: {
          alias: 'hello',
        },
        problems: [],
      },
    });

    expect(wrapper.text()).toBe(T.courseAssignmentProblemRunsEmpty);
  });
});
