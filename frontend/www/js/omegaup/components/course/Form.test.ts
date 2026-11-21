import { shallowMount } from '@vue/test-utils';
import type { types } from '../../api_types';

import T from '../../lang';

import MarkdownEditor from '../common/MarkdownEditor.vue';
import course_Form from './Form.vue';

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
    clarificationsPage: 1,
    clarificationsPageSize: 100,
    clarificationsPagerItems: [],
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

  it('Should render a Markdown editor for objective and description', () => {
    const wrapper = shallowMount(course_Form, {
      propsData: baseCourseFormProps,
    });

    const editors = wrapper.findAllComponents(MarkdownEditor);
    expect(editors).toHaveLength(2);
    expect(editors.at(0).props('idPostfix')).toBe('-objective');
    expect(editors.at(1).props('idPostfix')).toBe('-description');
  });

  it('Should make the Markdown editors read only', () => {
    const wrapper = shallowMount(course_Form, {
      propsData: Object.assign({}, baseCourseFormProps, { readOnly: true }),
    });

    const editors = wrapper.findAllComponents(MarkdownEditor);
    expect(editors.at(0).props('readOnly')).toBe(true);
    expect(editors.at(1).props('readOnly')).toBe(true);
  });
});
