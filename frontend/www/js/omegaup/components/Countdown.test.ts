import { shallowMount } from '@vue/test-utils';

import omegaup_Countdown from './Countdown.vue';

describe('Countdown.vue', () => {
  jest.useFakeTimers();
  it('Should handle a countdown with 10 seconds left to finish', async () => {
    const targetTime = new Date();
    const time = new Date();
    targetTime.setSeconds(targetTime.getSeconds() + 10);
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime,
        time,
      },
    });

    time.setSeconds(time.getSeconds() + 5);
    const mockedCurrentTime = time.getTime();
    const mockGetDateNowMethod = jest
      .spyOn(wrapper.vm, 'getDateNow')
      .mockImplementation(() => mockedCurrentTime);

    jest.advanceTimersByTime(5000);

    expect(mockGetDateNowMethod).toHaveBeenCalled();
    expect(wrapper.find('span').text()).toBe('00:00:10');

    mockGetDateNowMethod.mockRestore();
  });

  it('Should emit finish method', async () => {
    const date = new Date();
    const targetTime = date;
    const currentTime = date;
    targetTime.setSeconds(date.getSeconds() + 2);
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime,
      },
    });
    expect(wrapper.emitted('finish')).not.toBeDefined();

    currentTime.setSeconds(date.getSeconds() + 3);
    const mockedCurrentTime = currentTime.getTime();
    const mockGetDateNowMethod = jest
      .spyOn(wrapper.vm, 'getDateNow')
      .mockImplementation(() => mockedCurrentTime);
    await wrapper.setData({ currentTime });
    expect(wrapper.emitted('finish')).toBeDefined();

    mockGetDateNowMethod.mockRestore();
  });
});
