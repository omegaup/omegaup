import { ComponentOptions } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { omegaup } from '../../omegaup';
import arena_NavbarAssignments from './NavbarAssignments.vue';

describe('NavbarAssignments.vue', () => {
  const assignments: omegaup.Assignment[] = [
    {
      admin: false,
      alias: 'assignment-1',
      assignment_type: 'homework',
      description: 'First assignment',
      finish_time: new Date(),
      name: 'Assignment 1',
      order: 1,
      scoreboard_url: '',
      scoreboard_url_admin: '',
      start_time: new Date(),
    } as omegaup.Assignment,
    {
      admin: false,
      alias: 'assignment-2',
      assignment_type: 'homework',
      description: 'Second assignment',
      finish_time: new Date(),
      name: 'Assignment 2',
      order: 2,
      scoreboard_url: '',
      scoreboard_url_admin: '',
      start_time: new Date(),
    } as omegaup.Assignment,
    {
      admin: false,
      alias: 'assignment-3',
      assignment_type: 'homework',
      description: 'Third assignment',
      finish_time: new Date(),
      name: 'Assignment 3',
      order: 3,
      scoreboard_url: '',
      scoreboard_url_admin: '',
      start_time: new Date(),
    } as omegaup.Assignment,
  ];

  it('Should show only next button when on first assignment', async () => {
    const wrapper = shallowMount(
      arena_NavbarAssignments as ComponentOptions<Vue>,
      {
        propsData: {
          assignments,
          currentAssignment: assignments[0],
        },
      },
    );

    const buttons = wrapper.findAll('button');
    expect(buttons.length).toBe(1);
    expect(buttons.at(0).text()).toContain('Assignment 2');

    await buttons.at(0).trigger('click');
    expect(wrapper.emitted('navigate-to-assignment')).toEqual([
      ['assignment-2'],
    ]);
  });

  it('Should show both previous and next buttons when on middle assignment', async () => {
    const wrapper = shallowMount(
      arena_NavbarAssignments as ComponentOptions<Vue>,
      {
        propsData: {
          assignments,
          currentAssignment: assignments[1],
        },
      },
    );

    const buttons = wrapper.findAll('button');
    expect(buttons.length).toBe(2);
    expect(buttons.at(0).text()).toContain('Assignment 1');
    expect(buttons.at(1).text()).toContain('Assignment 3');

    await buttons.at(0).trigger('click');
    expect(wrapper.emitted('navigate-to-assignment')).toEqual([
      ['assignment-1'],
    ]);
  });

  it('Should show only previous button when on last assignment', async () => {
    const wrapper = shallowMount(
      arena_NavbarAssignments as ComponentOptions<Vue>,
      {
        propsData: {
          assignments,
          currentAssignment: assignments[2],
        },
      },
    );

    const buttons = wrapper.findAll('button');
    expect(buttons.length).toBe(1);
    expect(buttons.at(0).text()).toContain('Assignment 2');

    await buttons.at(0).trigger('click');
    expect(wrapper.emitted('navigate-to-assignment')).toEqual([
      ['assignment-2'],
    ]);
  });
});
