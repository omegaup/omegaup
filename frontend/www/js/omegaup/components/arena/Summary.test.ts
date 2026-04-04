import { shallowMount } from '@vue/test-utils';
import arena_Summary from './Summary.vue';
import T from '../../lang';

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
    expect(wrapper.text()).toContain(T.arenaPracticeStartTime);
  });

  it("Shouldn't show the summary of a course with unlimited duration", () => {
    const eventTitle = 'Event title';
    const wrapper = shallowMount(arena_Summary, {
      propsData: {
        title: eventTitle,
        description: 'Event description',
        startTime: new Date(),
        finishTime: null,
        scoreboard: null,
        windowLength: null,
        admin: 'omegaUp',
      },
    });

    expect(wrapper.text()).toContain(eventTitle);
    expect(wrapper.text()).not.toContain(T.arenaPracticeStartTime);
  });
});
