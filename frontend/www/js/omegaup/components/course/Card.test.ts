import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import course_Card from './Card.vue';

describe('Card.vue', () => {
  const course = {
    admission_mode: 'public',
    alias: 'test-course-alias',
    description: 'Test course description',
    finish_time: null,
    name: 'Test course name',
    school_name: 'Test school',
    progress: 0,
    is_open: false,
    accept_teacher: null,
  };

  it('Should render information for public course', () => {
    const wrapper = shallowMount(course_Card, {
      propsData: {
        course,
        type: 'public',
      },
    });

    expect(wrapper.text()).toContain(course.name);
  });

  it('Should render information for finished course', () => {
    const wrapper = shallowMount(course_Card, {
      propsData: {
        course,
        type: 'finished',
      },
    });

    expect(wrapper.text()).toContain(course.name);
    expect(wrapper.text()).toContain('⭐');
  });

  it('Should render information for student course', () => {
    const wrapper = shallowMount(course_Card, {
      propsData: {
        course,
        type: 'student',
      },
    });

    expect(wrapper.text()).toContain(course.name);
    expect(wrapper.text()).toContain(T.wordsProgress);
  });
});
