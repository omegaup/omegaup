import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import arena_NavbarProblems from './NavbarProblems.vue';

describe('NavbarProblems.vue', () => {
  it('Should handle breadcrumbs in course', () => {
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
          finish_time: new Date(),
          order: 1,
          start_time: new Date(),
          scoreboard_url: '',
          scoreboard_url_admin: '',
        } as omegaup.Assignment,
        digitsAfterDecimalPoint: 2,
        inAssignment: true,
        problems: [],
      },
    });

    expect(wrapper.find('div[data-breadcrumbs]').text()).toMatch(
      new RegExp(
        `${T.navCourses}.+?>.+?Curso de prueba.+?>.+?Tarea de prueba`,
        'ms',
      ),
    );
  });

  it('Should handle empty breadcrumbs in contest', async () => {
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

    expect(wrapper.find('div[data-breadcrumbs]').exists()).toBeFalsy();
  });
});
