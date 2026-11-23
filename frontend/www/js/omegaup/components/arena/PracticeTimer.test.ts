import { mount } from '@vue/test-utils';

import arena_PracticeTimer from './PracticeTimer.vue';

describe('PracticeTimer.vue', () => {
  const mountTimer = (problemAlias = 'problemA', isActive = true) =>
    mount(arena_PracticeTimer, {
      propsData: {
        contestAlias: 'contestA',
        problemAlias,
        isActive,
      },
    });

  beforeEach(() => {
    localStorage.clear();
    jest.useFakeTimers('modern');
  });

  afterEach(() => {
    jest.useRealTimers();
    localStorage.clear();
  });

  it('Should start automatically and support pause and resume', async () => {
    const wrapper = mountTimer();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:00');

    jest.advanceTimersByTime(3000);

    await wrapper.vm.$nextTick();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:03');

    await wrapper.find('.practice-timer__toggle').trigger('click');

    jest.advanceTimersByTime(2000);

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:03');

    expect(
      JSON.parse(
        localStorage.getItem('arena:practice-timer:contestA:problemA') || '{}',
      ).isPaused,
    ).toBe(true);

    await wrapper.find('.practice-timer__toggle').trigger('click');

    jest.advanceTimersByTime(2000);

    await wrapper.vm.$nextTick();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:05');

    wrapper.destroy();
  });

  it('Should preserve independent elapsed times for different problems', async () => {
    const firstProblem = mountTimer('problemA');

    jest.advanceTimersByTime(2000);

    firstProblem.destroy();

    const secondProblem = mountTimer('problemB');

    expect(secondProblem.find('.practice-timer__display').text()).toBe(
      '00:00:00',
    );

    secondProblem.destroy();

    const restoredProblem = mountTimer('problemA');

    await restoredProblem.vm.$nextTick();

    expect(restoredProblem.find('.practice-timer__display').text()).toBe(
      '00:00:02',
    );

    restoredProblem.destroy();
  });

  it('Should preserve manually paused state after remounting', async () => {
    const wrapper = mountTimer();

    jest.advanceTimersByTime(2000);

    await wrapper.find('.practice-timer__toggle').trigger('click');

    wrapper.destroy();

    const restored = mountTimer();

    await restored.vm.$nextTick();

    expect(
      JSON.parse(
        localStorage.getItem('arena:practice-timer:contestA:problemA') || '{}',
      ).isPaused,
    ).toBe(true);

    jest.advanceTimersByTime(3000);

    expect(restored.find('.practice-timer__display').text()).toBe('00:00:02');

    restored.destroy();
  });

  it('Should reset the timer without changing its running state', async () => {
    const wrapper = mountTimer();

    jest.advanceTimersByTime(3000);
    await wrapper.vm.$nextTick();

    await wrapper.find('.practice-timer__reset').trigger('click');

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:00');

    expect(
      JSON.parse(
        localStorage.getItem('arena:practice-timer:contestA:problemA') || '{}',
      ),
    ).toEqual({
      elapsedMs: 0,
      isPaused: false,
    });

    jest.advanceTimersByTime(2000);
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:02');

    await wrapper.find('.practice-timer__toggle').trigger('click');
    await wrapper.find('.practice-timer__reset').trigger('click');

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:00');

    expect(
      JSON.parse(
        localStorage.getItem('arena:practice-timer:contestA:problemA') || '{}',
      ),
    ).toEqual({
      elapsedMs: 0,
      isPaused: true,
    });

    jest.advanceTimersByTime(2000);
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:00');

    wrapper.destroy();
  });

  it('Should minimize and restore the timer', async () => {
    const wrapper = mountTimer();

    await wrapper.find('.practice-timer__minimize').trigger('click');

    expect(wrapper.classes()).toContain('practice-timer--minimized');
    expect(wrapper.find('.practice-timer__restore').exists()).toBe(true);
    expect(wrapper.find('.practice-timer__display').exists()).toBe(false);

    await wrapper.find('.practice-timer__restore').trigger('click');

    expect(wrapper.classes()).not.toContain('practice-timer--minimized');
    expect(wrapper.find('.practice-timer__display').exists()).toBe(true);
    expect(wrapper.find('.practice-timer__toggle').exists()).toBe(true);
    expect(wrapper.find('.practice-timer__reset').exists()).toBe(true);

    wrapper.destroy();
  });

  it('Should restore the previous expanded position', async () => {
    localStorage.setItem(
      'arena:practice-timer-position:contestA',
      JSON.stringify({ left: 120, top: 80 }),
    );

    const wrapper = mountTimer();

    await wrapper.vm.$nextTick();

    await wrapper.find('.practice-timer__minimize').trigger('click');
    await wrapper.vm.$nextTick();

    await wrapper.find('.practice-timer__restore').trigger('click');
    await wrapper.vm.$nextTick();

    expect(wrapper.element as HTMLElement).toHaveStyle({
      left: '120px',
      top: '80px',
    });

    wrapper.destroy();
  });

  it('Should save the timer position after dragging', () => {
    const wrapper = mountTimer();

    const rect = {
      x: 100,
      y: 100,
      left: 100,
      top: 100,
      right: 300,
      bottom: 180,
      width: 200,
      height: 80,
      toJSON: () => ({}),
    } as DOMRect;

    jest.spyOn(wrapper.element, 'getBoundingClientRect').mockReturnValue(rect);

    wrapper.find('.practice-timer__display').element.dispatchEvent(
      new MouseEvent('pointerdown', {
        bubbles: true,
        clientX: 110,
        clientY: 110,
      }),
    );

    window.dispatchEvent(
      new MouseEvent('pointermove', {
        clientX: 160,
        clientY: 160,
      }),
    );

    window.dispatchEvent(new MouseEvent('pointerup'));

    const savedPosition = JSON.parse(
      localStorage.getItem('arena:practice-timer-position:contestA') || '{}',
    );

    expect(savedPosition.left).toBe(150);
    expect(savedPosition.top).toBe(150);

    wrapper.destroy();
  });

  it('Should allow dragging the minimized timer', async () => {
    const wrapper = mountTimer();

    await wrapper.find('.practice-timer__minimize').trigger('click');

    const rect = {
      x: 100,
      y: 100,
      left: 100,
      top: 100,
      right: 148,
      bottom: 148,
      width: 48,
      height: 48,
      toJSON: () => ({}),
    } as DOMRect;

    jest.spyOn(wrapper.element, 'getBoundingClientRect').mockReturnValue(rect);

    wrapper.find('.practice-timer__restore').element.dispatchEvent(
      new MouseEvent('pointerdown', {
        bubbles: true,
        clientX: 110,
        clientY: 110,
      }),
    );

    window.dispatchEvent(
      new MouseEvent('pointermove', {
        clientX: 160,
        clientY: 160,
      }),
    );

    window.dispatchEvent(new MouseEvent('pointerup'));

    const savedPosition = JSON.parse(
      localStorage.getItem('arena:practice-timer-position:contestA') || '{}',
    );

    expect(savedPosition.left).toBe(150);
    expect(savedPosition.top).toBe(150);

    wrapper.destroy();
  });

  it('Should pause while the problems tab is inactive', async () => {
    const wrapper = mountTimer('problemA', false);

    jest.advanceTimersByTime(3000);
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:00');

    await wrapper.setProps({ isActive: true });

    jest.advanceTimersByTime(2000);
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:02');

    await wrapper.setProps({ isActive: false });

    jest.advanceTimersByTime(3000);
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:02');

    await wrapper.setProps({ isActive: true });

    jest.advanceTimersByTime(2000);
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.practice-timer__display').text()).toBe('00:00:04');

    wrapper.destroy();
  });
});
