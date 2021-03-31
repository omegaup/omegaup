jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';

import arena_Course from './Course.vue';

describe('Course.vue', () => {
  const currentAssignment = {
    alias: 'Tarea de prueba',
    assignment_type: 'homework',
    description: 'Descripción de la tarea de prueba',
    director: 'omegaUp',
    finish_time: new Date(),
    name: 'Tarea de prueba',
    problems: [],
    start_time: new Date(),
  };

  const course = {
    admission_mode: 'registration',
    alias: 'test-course',
    archived: false,
    assignments: [ currentAssignment ],
    clarifications: [],
    needs_basic_information: false,
    description: '# Test',
    finish_time: new Date(),
    is_curator: true,
    is_admin: true,
    name: 'Curso de prueba',
    public: true,
    requests_user_information: 'no',
    school_name: '',
    show_scoreboard: false,
    start_time: new Date(),
    student_count: 1,
    unlimited_duration: false,
  };

  it('Should handle course in arena', async () => {
    const wrapper = mount(arena_Course, {
      propsData: {
        activeTab: 'problems',
        clarifications: [],
        course,
        currentAssignment,
        guid: null,
        problem: null,
        problemAlias: null,
        problemInfo: null,
        problems: [],
        showNewClarificationPopup: false,
        socketConnected: true,
        users: [],
      },
    });

    expect(wrapper.find('h2').text()).toContain(currentAssignment.name);
    expect(wrapper.find('.clock').text()).not.toBe('∞');
  });
});
