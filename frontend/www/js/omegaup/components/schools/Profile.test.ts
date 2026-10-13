import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import schools_Profile from './Profile.vue';
import { SchoolCoderOfTheMonth, SchoolUser } from '../../linkable_resource';

describe('Profile.vue', () => {
  const defaultProps = {
    name: 'omegaUp School',
    rank: 3,
    country: { id: 'mx', name: 'México' },
    stateName: 'Puebla',
    monthlySolvedProblemsCount: [],
    users: [],
    codersOfTheMonth: new SchoolCoderOfTheMonth({
      classname: 'user-rank-unranked',
      time: '02/02/2022',
      username: 'omegaUp',
    }),
    chartOptions: {
      chart: {
        type: 'line',
      },
      title: {
        text:
          'Total de problemas resueltos mensualmente por los coders de omegaUp school',
      },
      yAxis: {
        min: 0,
        title: {
          text: 'Problemas resueltos',
        },
      },
      xAxis: {
        categories: ['2023-02', '2023-03'],
        title: {
          text: 'meses',
        },
        labels: {
          rotation: -45,
        },
      },
      legend: {
        enabled: false,
      },
      tooltip: {
        headerFormat: '',
        pointFormat: '<b>{point.y}<b/>',
      },
      series: [
        {
          data: 5,
        },
      ],
    },
  };

  it('Should handle profile view', () => {
    const wrapper = shallowMount(schools_Profile, {
      propsData: defaultProps,
    });

    expect(wrapper.find('h2').text()).toContain('omegaUp School');
    expect(wrapper.find('.rank-number').text()).toContain('#3');
    expect(wrapper.find('.list-group').text()).toContain('México');
    expect(wrapper.find('.list-group').text()).toContain('Puebla');
  });

  it('Should handle profile view without country or state', () => {
    const wrapper = shallowMount(schools_Profile, {
      propsData: {
        ...defaultProps,
        country: null,
        stateName: null,
        rank: 0,
      },
    });

    expect(wrapper.find('h2').text()).toBe('omegaUp School');
    expect(wrapper.find('.rank-number').exists()).toBe(false);
    expect(wrapper.find('.list-group').exists()).toBe(false);
  });

  it('Should sort users and handle updateUsers on sort option change', async () => {
    const user1 = new SchoolUser({
      classname: 'user-rank-master',
      username: 'user1',
      created_problems: 10,
      organized_contests: 5,
      solved_problems: 20,
    });
    const user2 = new SchoolUser({
      classname: 'user-rank-expert',
      username: 'user2',
      created_problems: 2,
      organized_contests: 1,
      solved_problems: 50,
    });

    const wrapper = shallowMount(schools_Profile, {
      propsData: {
        ...defaultProps,
        users: [user1, user2],
      },
    });

    // Initially sorted by solved_problems: user2 (50) > user1 (20)
    const vm = wrapper.vm as any;
    expect(vm.schoolUsers[0].username).toBe('user2');
    expect(vm.schoolUsers[1].username).toBe('user1');
    expect(vm.sortByTableTitle).toBe(T.profileSolvedProblems);

    // Call updateUsers to sort by created_problems
    vm.updateUsers('created_problems');
    await wrapper.vm.$nextTick();

    expect(vm.sortBy).toBe('created_problems');
    expect(vm.sortByTableTitle).toBe(T.profileCreatedProblems);
    expect(user1.displayField).toBe('created_problems');
    expect(user2.displayField).toBe('created_problems');
    // Now user1 (10) > user2 (2)
    expect(vm.schoolUsers[0].username).toBe('user1');
    expect(vm.schoolUsers[1].username).toBe('user2');

    // Call updateUsers to sort by organized_contests
    vm.updateUsers('organized_contests');
    await wrapper.vm.$nextTick();
    expect(vm.sortByTableTitle).toBe(T.profileOrganizedContests);
  });
});
