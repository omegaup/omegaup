import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import course_AssignmentDetails from './AssignmentDetails.vue';

describe('AssignmentDetails.vue', () => {
  it('Should handle empty assignments and progress as admin', () => {
    const wrapper = shallowMount(course_AssignmentDetails, {
      propsData: {
        assignment: {} as omegaup.Assignment,
        assignmentFormMode: omegaup.AssignmentFormMode.New,
        finishTimeCourse: new Date(),
        startTimeCourse: new Date(),
        unlimitedDurationCourse: false,
        invalidParameterName: '',
      },
    });

    expect(
      wrapper.find('form.schedule button[type=submit].submit').text(),
    ).toBe(T.courseAssignmentNewFormSchedule);
  });
});
