import { shallowMount } from '@vue/test-utils';

import omegaup_Countdown from './Countdown.vue';
describe('Countdown.vue', () => {
  let now = Date.now();
  let dateNowSpy: jest.SpyInstance<number, []> | null = null;

  beforeEach(() => {
    dateNowSpy = jest.spyOn(Date, 'now').mockImplementation(() => now);
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.runOnlyPendingTimers();
    jest.useRealTimers();
    if (dateNowSpy) {
      dateNowSpy.mockRestore();
    }
  });

  it('Should handle a countdown with 5 seconds left to finish', async () => {
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now + 10000),
      },
    });

    const timeDelta = 5000;
    now += timeDelta;
    expect(wrapper.find('span').text()).toBe('00:00:10');
    await jest.advanceTimersByTime(timeDelta);
    expect(wrapper.find('span').text()).toBe('00:00:05');
  });

  it('Should emit finish method', async () => {
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now + 1000),
      },
    });
    expect(wrapper.emitted('finish')).not.toBeDefined();

    const timeDelta = 3000;
    now += timeDelta;
    await jest.advanceTimersByTime(timeDelta);
    expect(wrapper.emitted('finish')).toBeDefined();
  });
});
