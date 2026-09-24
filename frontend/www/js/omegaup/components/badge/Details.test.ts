import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import * as time from '../../time';
import badge_Details from './Details.vue';

describe('Details.vue', () => {
  const badgeAlias = 'contestManager';

  it('Should display the badge icon and the owners count', () => {
    const wrapper = shallowMount(badge_Details, {
      propsData: {
        badge: {
          badge_alias: badgeAlias,
          owners_count: 3,
          total_users: 10,
        },
      },
    });

    expect(wrapper.find('img').attributes().src).toBe(
      `/media/dist/badges/${badgeAlias}.svg`,
    );
    expect(wrapper.find('.badge-data').text()).toBe('3/10');
  });

  it('Should display the assignation dates when the badge is owned', () => {
    const firstAssignation = new Date(2020, 0, 15);
    const assignationTime = new Date(2021, 5, 20);
    const wrapper = shallowMount(badge_Details, {
      propsData: {
        badge: {
          badge_alias: badgeAlias,
          owners_count: 1,
          total_users: 1,
          first_assignation: firstAssignation,
          assignation_time: assignationTime,
        },
      },
    });

    expect(wrapper.text()).toContain(time.formatDate(firstAssignation));
    expect(wrapper.text()).toContain(time.formatDate(assignationTime));
    expect(
      wrapper.find('omegaup-markdown-stub').attributes().markdown,
    ).toContain(T.badgeAssignationTimeMessage);
  });

  it('Should display the not assigned message when the badge is not owned', () => {
    const wrapper = shallowMount(badge_Details, {
      propsData: {
        badge: {
          badge_alias: badgeAlias,
          owners_count: 0,
          total_users: 5,
        },
      },
    });

    expect(
      wrapper.find('omegaup-markdown-stub').attributes().markdown,
    ).toContain(T.badgeNotAssignedMessage);
    expect(wrapper.findAll('.badge-data').at(1).text()).toBe('');
  });

  it('Should gray out the icon when the badge is not owned', () => {
    const wrapper = shallowMount(badge_Details, {
      propsData: {
        badge: {
          badge_alias: badgeAlias,
          owners_count: 0,
          total_users: 5,
        },
      },
    });

    expect(wrapper.find('img').classes()).toContain('badge-icon-gray');
  });
});
