import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';
import * as time from '../../time';

import badge_Details from './Details.vue';

const Details = badge_Details as ComponentOptions<Vue>;

describe('Details.vue', () => {
  const badgeAlias = 'contestManager';
  const firstAssignation = new Date('2020-01-15T00:00:00Z');
  const assignationTime = new Date('2021-06-01T00:00:00Z');

  it('Should render badge details when assigned', () => {
    const wrapper = shallowMount(Details, {
      propsData: {
        badge: {
          badge_alias: badgeAlias,
          assignation_time: assignationTime,
          first_assignation: firstAssignation,
          owners_count: 10,
          total_users: 100,
        },
      },
    });

    expect(wrapper.find('h1').text()).toBe(T[`badge_${badgeAlias}_name`]);
    expect(wrapper.text()).toContain(T[`badge_${badgeAlias}_description`]);
    expect(wrapper.text()).toContain('10/100');
    expect(wrapper.text()).toContain(time.formatDate(firstAssignation));
    expect(wrapper.text()).toContain(time.formatDate(assignationTime));
    expect(wrapper.find('img').attributes().src).toBe(
      `/media/dist/badges/${badgeAlias}.svg`,
    );
    expect(wrapper.find('img').classes()).not.toContain('badge-icon-gray');
  });

  it('Should show gray icon when badge is not assigned', () => {
    const wrapper = shallowMount(Details, {
      propsData: {
        badge: {
          badge_alias: badgeAlias,
          assignation_time: null,
          first_assignation: null,
          owners_count: 0,
          total_users: 100,
        },
      },
    });

    expect(wrapper.find('img').classes()).toContain('badge-icon-gray');
    expect(wrapper.text()).not.toContain(time.formatDate(assignationTime));
  });
});
