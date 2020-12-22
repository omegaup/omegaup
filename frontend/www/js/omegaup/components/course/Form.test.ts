import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import expect from 'expect';

import T from '../../lang';

import course_Form from './Form.vue';

const baseCourseFormProps = {
  course: <types.CourseDetails>{
    admission_mode: 'registration',
    alias: 'Newx',
    assignments: [],
    needs_basic_information: false,
    description: 'New',
    finish_time: new Date(),
    is_admin: true,
    is_curator: true,
    name: 'Nuevo',
    requests_user_information: 'no',
    school_id: 1,
    school_name: 'Escuela curso',
    show_scoreboard: false,
    start_time: new Date(),
    student_count: 3,
    unlimited_duration: false,
  },
  update: true,
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
});
