import { ComponentOptions } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { omegaup } from '../../omegaup';
import arena_NavbarMiniranking from './NavbarMiniranking.vue';

describe('NavbarMiniranking.vue', () => {
  const users: omegaup.UserRank[] = [
    {
      classname: 'user-rank-master',
      country: 'MX',
      penalty: 15,
      points: 100,
      position: 1,
      username: 'user_master',
    },
    {
      classname: 'user-rank-expert',
      country: 'US',
      penalty: 45,
      points: 80,
      position: 2,
      username: 'user_expert',
    },
  ];

  it('Should not render table when showRanking is false', () => {
    const wrapper = shallowMount(
      arena_NavbarMiniranking as ComponentOptions<Vue>,
      {
        propsData: {
          showRanking: false,
          users,
        },
      },
    );

    expect(wrapper.find('table.mini-ranking').exists()).toBe(false);
  });

  it('Should render table and user rows when showRanking is true', () => {
    const wrapper = shallowMount(
      arena_NavbarMiniranking as ComponentOptions<Vue>,
      {
        propsData: {
          showRanking: true,
          users,
        },
      },
    );

    expect(wrapper.find('table.mini-ranking').exists()).toBe(true);
    const rows = wrapper.findAll('tbody.user-list-template tr');
    expect(rows.length).toBe(2);

    expect(rows.at(0).find('td.position').text()).toBe('1');
    expect(rows.at(0).find('td.points').text()).toBe('100.00');
    expect(rows.at(0).find('td.penalty').text()).toBe('15');

    expect(rows.at(1).find('td.position').text()).toBe('2');
    expect(rows.at(1).find('td.points').text()).toBe('80.00');
    expect(rows.at(1).find('td.penalty').text()).toBe('45');
  });
});
