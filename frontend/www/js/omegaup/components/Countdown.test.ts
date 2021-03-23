import { shallowMount } from '@vue/test-utils';

import omegaup_Countdown from './Countdown.vue';

describe('Countdown.vue', () => {
  it('Should emit finish method', async () => {
    const date = new Date();
    const currentTime = new Date();
    date.setSeconds(date.getSeconds() + 2);
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: date,
      },
    });
    expect(wrapper.emitted('finish')).not.toBeDefined();

    currentTime.setSeconds(date.getSeconds() + 1);
    await wrapper.setData({ currentTime });
    expect(wrapper.emitted('finish')).toBeDefined();
  });
});
