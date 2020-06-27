import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import arena_NavbarProblems from './NavbarProblems.vue';

describe('NavbarProblems.vue', () => {
  it('Should handle empty problems in course', () => {
    const wrapper = shallowMount(arena_NavbarProblems, {
      propsData: {
        activeProblem: null,
        courseAlias: 'curso-prueba',
        courseName: 'Curso de prueba',
        currentAssignment: {
          admin: true,
          alias: 'tarea-prueba',
          assignment_type: 'homework',
          courseAssignments: [],
          description: 'Tarea de prueba',
          director: 'omegaup',
          name: 'Tarea de prueba',
          problems: [],
          problemset_id: 2,
          status: 'ok',
        },
        digitsAfterDecimalPoint: 2,
        inAssignment: true,
        problems: [],
      },
    });

    expect(wrapper.find('.breadcurums').text()).toBe(
      `${T.navCourses} > Curso de prueba > Tarea de prueba`,
    );
  });

  it('Should handle empty problems in contest', async () => {
    const wrapper = shallowMount(arena_NavbarProblems, {
      propsData: {
        activeProblem: null,
        contestAlias: 'concurso-prueba',
        courseName: 'Concurso de prueba',
        digitsAfterDecimalPoint: 2,
        inAssignment: false,
        problems: [],
      },
    });

    expect(wrapper.find('.breadcurums').exists()).toBeFalsy();
  });
});
