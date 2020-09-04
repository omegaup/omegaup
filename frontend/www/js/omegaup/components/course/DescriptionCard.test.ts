import { mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import course_DesciptionCard from './DescriptionCard.vue';

describe('DescriptionCard.vue', () => {
  const wrapper = mount(course_DesciptionCard);

  expect(wrapper.text()).toContain(T.courseCardAboutCourses);
  expect(wrapper.text()).toContain(T.courseCardDescriptionCourses);
  expect(wrapper.text()).toContain(T.wordsReadMore);
});
