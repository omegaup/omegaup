import { shallowMount } from '@vue/test-utils';
import common_StreakIndicator, { getStreakEmoji } from './StreakIndicator.vue';
import T from '../../lang';
import * as ui from '../../ui';

describe('StreakIndicator.vue', () => {
  describe('getStreakEmoji', () => {
    it.each([
      [0, '❄️'],
      [-1, '❄️'],
      [1, '🌱'],
      [6, '🌱'],
      [7, '✨'],
      [14, '✨'],
      [15, '⚡'],
      [29, '⚡'],
      [30, '🔥'],
      [100, '🔥'],
    ])('maps streak %d to emoji %s', (streak, expectedEmoji) => {
      expect(getStreakEmoji(streak)).toBe(expectedEmoji);
    });
  });

  describe('component rendering', () => {
    it.each([
      [0, '❄️'],
      [1, '🌱'],
      [6, '🌱'],
      [7, '✨'],
      [14, '✨'],
      [15, '⚡'],
      [29, '⚡'],
      [30, '🔥'],
    ])(
      'renders streak %d with emoji %s and tooltip',
      (streak, expectedEmoji) => {
        const wrapper = shallowMount(common_StreakIndicator, {
          propsData: { streak },
        });

        const indicator = wrapper.find('[data-streak-indicator]');
        expect(indicator.exists()).toBe(true);

        const emojiSpan = wrapper.find('.streak-emoji');
        expect(emojiSpan.text()).toBe(expectedEmoji);
        expect(emojiSpan.attributes('aria-hidden')).toBe('true');

        expect(wrapper.find('.streak-count').text()).toBe(String(streak));
        expect(wrapper.text()).toContain(`${expectedEmoji} ${streak}`);

        const expectedTooltip =
          streak === 1
            ? ui.formatString(T.streakIndicatorTooltipOne, { count: streak })
            : ui.formatString(T.streakIndicatorTooltip, { count: streak });
        expect(indicator.attributes('title')).toBe(expectedTooltip);
        expect(indicator.attributes('aria-label')).toBe(expectedTooltip);
      },
    );
  });
});
