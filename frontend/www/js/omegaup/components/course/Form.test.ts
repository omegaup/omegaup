import { shallowMount } from '@vue/test-utils';
import type { types } from '../../api_types';

import T from '../../lang';
import * as ui from '../../ui';

import course_Form from './Form.vue';

jest.mock('../../ui');

beforeEach(() => {
  jest.clearAllMocks();
});

const baseCourseFormProps = {
  allLanguages: { py2: 'Python 2', py3: 'Python 3' },
  course: {
    admission_mode: 'registration',
    alias: 'Newx',
    archived: false,
    assignments: [],
    clarifications: [],
    needs_basic_information: false,
    description: 'New',
    finish_time: new Date(),
    is_admin: true,
    is_curator: true,
    is_teaching_assistant: false,
    name: 'Nuevo',
    objective: 'Objetivo de prueba',
    level: '',
    recommended: false,
    requests_user_information: 'no',
    school_id: 1,
    school_name: 'Escuela curso',
    show_scoreboard: false,
    start_time: new Date(),
    student_count: 3,
    unlimited_duration: false,
    languages: ['py2'],
    teaching_assistant_enabled: false,
  } as types.CourseDetails,
  update: true,
  searchResultSchools: [
    { key: 1, value: 'New school' },
  ] as types.SchoolListItem[],
};
const selector = '.omegaup-course-details button.btn-primary';

describe('Form.vue', () => {
  it('Should handle course edit form', () => {
    const wrapper = shallowMount(course_Form, {
      propsData: baseCourseFormProps,
    });

    expect(wrapper.find(selector).text()).toBe(T.courseNewFormUpdateCourse);
  });

  it('Should handle admission mode as normal user', () => {
    const wrapper = shallowMount(course_Form, {
      propsData: Object.assign({}, baseCourseFormProps, { update: false }),
    });

    expect(wrapper.find(selector).text()).toBe(T.courseNewFormScheduleCourse);
  });

  it('Should show error when submitting form with no languages selected', async () => {
    const wrapper = shallowMount(course_Form, {
      propsData: {
        ...baseCourseFormProps,
        course: {
          ...baseCourseFormProps.course,
          languages: [],
        },
      },
    });

    await wrapper.find('form').trigger('submit.prevent');

    expect(ui.error).toHaveBeenCalledWith(
      T.courseNewFormSelectAtLeastOneLanguage,
    );
    expect(wrapper.emitted('submit')).toBeUndefined();
  });

  it('Should emit submit event when form has languages selected', async () => {
    const wrapper = shallowMount(course_Form, {
      propsData: baseCourseFormProps,
    });

    await wrapper.find('form').trigger('submit.prevent');

    expect(ui.error).not.toHaveBeenCalled();
    expect(wrapper.emitted('submit')).toBeDefined();
  });
});
