import { shallowMount } from '@vue/test-utils';

import omegaup_Countdown from './Countdown.vue';

describe('Countdown.vue', () => {
  it('Should emit finish method', async () => {
    const date = new Date();
    date.setSeconds(date.getSeconds() + 2);
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: date,
      },
    });

    setTimeout(() => {
      expect(wrapper.emitted('finish')).toBeDefined();
    }, 3000);
  });
});
