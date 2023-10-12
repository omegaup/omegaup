import { shallowMount } from '@vue/test-utils';
import badge_Badge from './Badge.vue';

describe('Badge.vue', () => {
  it('Should display badge name', () => {
    const badgeAlias = 'contestManager';
    const wrapper = shallowMount(badge_Badge, {
      propsData: {
        badge: { badge_alias: badgeAlias },
      },
    });
    expect(wrapper.find('img').attributes().src).toBe(
      `https://omegaup.com/media/dist/badges/${badgeAlias}.svg`,
    );
  });
});
