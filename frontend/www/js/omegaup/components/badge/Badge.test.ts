import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import badge_Badge from './Badge.vue';

const Badge = (badge_Badge as unknown) as ComponentOptions<Vue>;

describe('Badge.vue', () => {
  it('Should display badge name', () => {
    const badgeAlias = 'contestManager';
    const wrapper = shallowMount(Badge, {
      propsData: {
        badge: { badge_alias: badgeAlias },
      },
    });
    expect(wrapper.find('img').attributes().src).toBe(
      `/media/dist/badges/${badgeAlias}.svg`,
    );
  });
});
