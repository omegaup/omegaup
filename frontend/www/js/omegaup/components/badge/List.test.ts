import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import { omegaup } from '../../omegaup';

import badge_Badge from './Badge.vue';

describe('Badgev2.vue', () => {
  it('Should display badge name', () => {
    const badgeAlias = 'contestManager';
    const wrapper = shallowMount(badge_Badge, {
      propsData: {
        badge: { badge_alias: badgeAlias },
      },
    });
    expect(wrapper.find('img').attributes().src).toBe(
      `/media/dist/badges/${badgeAlias}.svg`,
    );
  });
});
