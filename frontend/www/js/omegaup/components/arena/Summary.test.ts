import { shallowMount } from '@vue/test-utils';
import arena_Summary from './Summary.vue';

describe('Summary.vue', () => {
  it('Should handle summary', () => {
    const eventTitle = 'Event title';
    const wrapper = shallowMount(arena_Summary, {
      propsData: {
        title: eventTitle,
        description: 'Event description',
        startTime: new Date(),
        finishTime: new Date(),
        scoreboard: null,
        windowLength: null,
        admin: 'omegaUp',
      },
    });

    expect(wrapper.text()).toContain(eventTitle);
  });
});
