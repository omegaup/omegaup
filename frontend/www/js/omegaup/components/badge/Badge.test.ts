import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import badge_Badge from './Badge.vue';

describe('Badge.vue', () => {
  it('Should display badge name', () => {
    const badge_alias = 'contestManager';
    const wrapper = shallowMount(badge_Badge, {
      propsData: {
        badge: { badge_alias: badge_alias },
      },
    });
    expect(wrapper.find('img').attributes().src).toBe(
      `/media/dist/badges/${badge_alias}.svg`,
    );
  });
});
