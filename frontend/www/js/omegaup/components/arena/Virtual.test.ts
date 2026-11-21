import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import arena_Virtual from './Virtual.vue';

describe('Virtual.vue', () => {
  const startTime = new Date(2026, 0, 1, 10, 0, 0);
  const finishTime = new Date(2026, 0, 1, 15, 0, 0);

  it('Should render virtual contest details and rules correctly', () => {
    const wrapper = shallowMount(arena_Virtual as ComponentOptions<Vue>, {
      propsData: {
        title: 'Virtual Contest Title',
        description: 'Virtual contest description for participants',
        startTime,
        finishTime,
        scoreboard: '100',
        submissionsGap: 1200,
      },
    });

    expect(wrapper.text()).toContain('Virtual Contest Title');
    expect(wrapper.text()).toContain(
      'Virtual contest description for participants',
    );
    expect(wrapper.find('button[data-schedule-virtual-button]').exists()).toBe(
      true,
    );
  });

  it('Should emit submit event with virtualContestStartTime when form is submitted', async () => {
    const wrapper = shallowMount(arena_Virtual as ComponentOptions<Vue>, {
      propsData: {
        title: 'Virtual Contest Title',
        description: 'Virtual contest description for participants',
        startTime,
        finishTime,
        scoreboard: '100',
        submissionsGap: 1200,
      },
    });

    await wrapper.find('form').trigger('submit.prevent');
    const emitted = wrapper.emitted('submit');
    expect(emitted).toBeTruthy();
    expect(emitted?.[0]?.[0]).toHaveProperty('virtualContestStartTime');
    expect(emitted?.[0]?.[0].virtualContestStartTime).toBeInstanceOf(Date);
  });
});
