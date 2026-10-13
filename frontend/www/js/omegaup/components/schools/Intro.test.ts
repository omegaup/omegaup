import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import schools_Intro from './Intro.vue';

describe('Intro.vue', () => {
  it('Should render header title and course link', () => {
    const wrapper = shallowMount(schools_Intro);

    expect(wrapper.find('h1').text()).toBe(T.schoolsLearnProgBySolvProblems);
    const courseLink = wrapper.find('a.btn-primary');
    expect(courseLink.text()).toBe(T.schoolsStartUsingCourses);
    expect(courseLink.attributes('href')).toBe('/course/');
  });

  it('Should render feature thumbnails and descriptions', () => {
    const wrapper = shallowMount(schools_Intro);

    const thumbnails = wrapper.findAll('.thumbnail');
    expect(thumbnails.length).toBe(3);

    const headings = wrapper.findAll('.caption h3');
    expect(headings.at(0).text()).toBe(T.schoolsCreateYourCourse);
    expect(headings.at(1).text()).toBe(T.schoolsManageProgressOfStudents);
    expect(headings.at(2).text()).toBe(T.schoolsCreateYourProblems);

    const descriptions = wrapper.findAll('.caption p');
    expect(descriptions.length).toBe(3);
    descriptions.wrappers.forEach((desc) => {
      expect(desc.text()).toBe(T.schoolsCreateCoursesThatMeetNeeds);
    });
  });
});
