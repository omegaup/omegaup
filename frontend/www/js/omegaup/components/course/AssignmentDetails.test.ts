import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import course_AssignmentDetails from './AssignmentDetails.vue';

describe('AssignmentDetails.vue', () => {
  beforeEach(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
  });

  afterAll(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.removeChild(rootDiv);
    }
  });

  const propsData = {
    assignment: {} as omegaup.Assignment,
    assignmentFormMode: omegaup.AssignmentFormMode.New,
    finishTimeCourse: new Date(),
    startTimeCourse: new Date(),
    unlimitedDurationCourse: false,
    invalidParameterName: '',
    courseAlias: 'course_alias',
  };

  it('Should handle empty assignments and progress as admin', () => {
    const wrapper = shallowMount(course_AssignmentDetails, { propsData });

    expect(wrapper.find('form button[type=submit].submit').text()).toBe(
      T.courseAssignmentNewFormSchedule,
    );
  });

  it('Should handle submit action for a new assignment', async () => {
    const wrapper = shallowMount(course_AssignmentDetails, {
      attachTo: '#root',
      propsData,
    });

    await wrapper.setData({
      name: 'new assignment',
      alias: 'na',
      description: 'description',
    });
    await wrapper.find('form button[type=submit].submit').trigger('click');
    expect(wrapper.emitted('add-assignment')).toBeDefined();
    expect(wrapper.emitted('add-assignment')).toEqual([
      [
        {
          alias: 'na',
          assignment_type: 'homework',
          course_alias: 'course_alias',
          description: 'description',
          name: 'new assignment',
          problems: '[]',
          start_time: expect.any(Number),
          unlimited_duration: true,
        },
      ],
    ]);

    wrapper.destroy();
  });

  it('Should handle submit action for an existing assignment', async () => {
    const wrapper = shallowMount(course_AssignmentDetails, {
      attachTo: '#root',
      propsData: {
        ...propsData,
        ...{
          assignmentFormMode: omegaup.AssignmentFormMode.Edit,
          assignment: {
            alias: 'assignment_alias',
            description: 'description',
            name: 'original name',
          },
        },
      },
    });

    await wrapper.setData({
      name: 'updated name',
      description: 'updated description',
    });
    await wrapper.find('form button[type=submit].submit').trigger('click');
    expect(wrapper.emitted('update-assignment')).toBeDefined();
    expect(wrapper.emitted('update-assignment')).toEqual([
      [
        {
          assignment: 'assignment_alias',
          assignment_type: 'homework',
          course: 'course_alias',
          description: 'updated description',
          name: 'updated name',
          start_time: expect.any(Number),
          unlimited_duration: true,
        },
      ],
    ]);

    wrapper.destroy();
  });

  it('Should handle submit action for an existing assignment with runs', async () => {
    const wrapper = shallowMount(course_AssignmentDetails, {
      attachTo: '#root',
      propsData: {
        ...propsData,
        ...{
          assignmentFormMode: omegaup.AssignmentFormMode.Edit,
          assignment: {
            alias: 'assignment_alias',
            description: 'description',
            name: 'original name',
            has_runs: true,
          },
        },
      },
    });

    await wrapper.setData({
      name: 'updated name',
      description: 'updated description',
    });
    await wrapper.find('form button[type=submit].submit').trigger('click');
    expect(wrapper.emitted('update-assignment')).toBeDefined();
    expect(wrapper.emitted('update-assignment')).toEqual([
      [
        {
          assignment: 'assignment_alias',
          assignment_type: 'homework',
          course: 'course_alias',
          description: 'updated description',
          name: 'updated name',
          unlimited_duration: true,
        },
      ],
    ]);

    wrapper.destroy();
  });
});
