import { shallowMount } from '@vue/test-utils';

import schools_Profile from './Profile.vue';
import { SchoolCoderOfTheMonth } from '../../linkable_resource';

describe('Profile.vue', () => {
  it('Should handle profile view', () => {
    const wrapper = shallowMount(schools_Profile, {
      propsData: {
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
            text: 'Total de problemas resueltos mensualmente por los coders de omegaUp school',
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
      },
    });

    expect(wrapper.find('h2').text()).toContain('omegaUp School');
    expect(wrapper.find('.rank-number').text()).toContain('#3');
  });
});
