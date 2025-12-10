import { shallowMount } from '@vue/test-utils';

import type { types } from '../../api_types';

import course_Edit from './Edit.vue';

const noop = () => {};
Object.defineProperty(window, 'scrollTo', { value: noop, writable: true });

describe('Edit.vue', () => {
  it('Should handle assignments', async () => {
    const courseName = 'Test course';
    const wrapper = shallowMount(course_Edit, {
      propsData: {
        data: {
          course: {
            admission_mode: 'registration',
            alias: 'test-course',
            archived: false,
            assignments: [
              {
                problemset_id: 1,
                alias: 'alias',
                description: 'description',
                name: 'name',
                has_runs: false,
                max_points: 0,
                start_time: new Date(),
                finish_time: new Date(),
                opened: false,
                order: 1,
                scoreboard_url: 'sb_url',
                scoreboard_url_admin: 'sc_url_admin',
                assignment_type: 'test',
                problemCount: 0,
              },
            ],
            clarifications: [],
            objective: '',
            level: '',
            needs_basic_information: false,
            description: '# Test',
            finish_time: undefined,
            is_curator: true,
            is_admin: true,
            is_teaching_assistant: false,
            name: courseName,
            public: true,
            recommended: false,
            requests_user_information: 'no',
            school_name: '',
            show_scoreboard: false,
            start_time: new Date(),
            student_count: 1,
            unlimited_duration: false,
            teaching_assistant_enabled: false,
          },
          allLanguages: { kp: 'Karel Pascal', kj: 'Karel Java' },
          assignmentProblems: [],
          selectedAssignment: undefined,
          students: [],
          identityRequests: [],
          admins: [],
          groupsAdmins: [],
          groupsTeachingAssistants: [],
          teachingAssistants: [],
          isTeachingAssistant: false,
          isAdmin: true,
          tags: [],
        } as types.CourseEditPayload,
        initialTab: 'course',
      },
    });

    expect(wrapper.text()).toContain(courseName);

    // All the links are available
    await wrapper.find('a[data-tab-content]').trigger('click');
    await wrapper.find('a[data-tab-admission-mode]').trigger('click');
    await wrapper.find('a[data-tab-students]').trigger('click');
    await wrapper.find('a[data-tab-admins]').trigger('click');
    await wrapper.find('a[data-tab-clone]').trigger('click');
    await wrapper.find('a[data-tab-course]').trigger('click');
    await wrapper.find('a[data-tab-archive]').trigger('click');
  });
});
