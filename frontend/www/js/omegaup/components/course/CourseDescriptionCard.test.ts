import { mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import course_DesciptionCard from './CourseDescriptionCard.vue';

describe('CourseDescriptionCard.vue', () => {
    
      const wrapper = mount(course_DesciptionCard);
  
      expect(wrapper.text()).toContain(T.courseCardAboutCourses);
      expect(wrapper.text()).toContain(T.courseCardAboutCourses);
      expect(wrapper.text()).toContain(T.wordsReadMore);

  });