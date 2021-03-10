import { shallowMount } from '@vue/test-utils';
import arena_Summary from './Summary.vue';

describe('Summary.vue', () => {
  it('Should handle summary', () => {
    const event = {
      title: 'Event title',
      description: 'Event description',
    };
    const wrapper = shallowMount(arena_Summary, {
      propsData: {
        title: event.title,
        description: event.description,
        startTime: new Date(),
        finishTime: new Date(),
        scoreboard: null,
        windowLength: null,
        admin: 'omegaUp',
      },
    });

    expect(wrapper.text()).toContain(event.title);
    expect(wrapper.text()).toContain(event.description);
  });
});
