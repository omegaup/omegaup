import { shallowMount } from '@vue/test-utils';

import omegaup_Countdown from './Countdown.vue';
import { omegaup } from '../omegaup';
import * as ui from '../ui';
import T from '../lang';
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

  it('Should handle countdown in mode ContestHasNotStarted', async () => {
    const seconds = 10;
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now + 1000 * seconds),
        countdownFormat: omegaup.CountdownFormat.ContestHasNotStarted,
      },
    });
    expect(wrapper.find('span').text()).toBe(
      ui.formatString(T.contestWillBeginIn, { time: '00:00:10' }),
    );
  });

  it('Should handle countdown in mode ContestHasNotStarted when contest has started', async () => {
    const seconds = 10;
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now - 1000 * seconds),
        countdownFormat: omegaup.CountdownFormat.ContestHasNotStarted,
      },
    });
    expect(wrapper.find('span').text()).toBe(T.arenaContestHasAlreadyStarted);
  });

  it('Should handle countdown in mode AssignmentHasNotStarted', async () => {
    const seconds = 10;
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now + 1000 * seconds),
        countdownFormat: omegaup.CountdownFormat.AssignmentHasNotStarted,
      },
    });
    expect(wrapper.find('span').text()).toBe(
      ui.formatString(T.arenaCourseAssignmentWillBeginIn, { time: '00:00:10' }),
    );
  });

  it('Should handle countdown in mode AssignmentHasNotStarted when assignment has started', async () => {
    const seconds = 10;
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now - 1000 * seconds),
        countdownFormat: omegaup.CountdownFormat.AssignmentHasNotStarted,
      },
    });
    expect(wrapper.find('span').text()).toBe(
      T.arenaCourseAssignmentHasAlreadyStarted,
    );
  });

  it('Should handle countdown in mode WaitBetweenUploadsSeconds', async () => {
    const seconds = 10;
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now + 1000 * seconds),
        countdownFormat: omegaup.CountdownFormat.WaitBetweenUploadsSeconds,
      },
    });
    expect(wrapper.find('span').text()).toBe(
      ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
        submissionGap: seconds,
      }),
    );
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

  it('Should clear interval when component is destroyed before countdown finishes', () => {
    const clearIntervalSpy = jest.spyOn(window, 'clearInterval');
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now + 60000),
      },
    });

    expect((wrapper.vm as any).timerInterval).not.toBe(0);
    wrapper.destroy();
    expect(clearIntervalSpy).toHaveBeenCalled();
    expect((wrapper.vm as any).timerInterval).toBe(0);
    clearIntervalSpy.mockRestore();
  });

  it('Should handle destroy gracefully when countdown has already finished', () => {
    const clearIntervalSpy = jest.spyOn(window, 'clearInterval');
    const wrapper = shallowMount(omegaup_Countdown, {
      propsData: {
        targetTime: new Date(now - 5000),
      },
    });

    // With fake timers, the watcher hasn't fired yet, so the interval
    // is still active. beforeDestroy should clean it up.
    wrapper.destroy();
    expect(clearIntervalSpy).toHaveBeenCalled();
    expect((wrapper.vm as any).timerInterval).toBe(0);
    clearIntervalSpy.mockRestore();
  });
});
