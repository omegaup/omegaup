import { shallowMount } from '@vue/test-utils';
import user_Dependents from './Dependents.vue';
import T from '../../lang';
import * as ui from '../../ui';

describe('Dependents.vue', () => {
  const now = new Date('2024-01-20');
  let dateNowSpy: jest.SpyInstance<number, []> | null = null;

  beforeEach(() => {
    dateNowSpy = jest
      .spyOn(Date, 'now')
      .mockImplementation(() => now.getTime());
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.runOnlyPendingTimers();
    jest.useRealTimers();
    if (dateNowSpy) {
      dateNowSpy.mockRestore();
    }
  });
  it('calculates the correct number of days until verification deadline', () => {
    const userVerificationDeadline = new Date('2024-01-31');
    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        userVerificationDeadline,
      },
    });

    const expectedDaysDifference = 11;

    expect(wrapper.vm.daysUntilVerificationDeadline).toBe(
      expectedDaysDifference,
    );
  });

  it('returns null when userVerificationDeadline is not set', () => {
    const wrapper = shallowMount(user_Dependents);

    expect(wrapper.vm.daysUntilVerificationDeadline).toBeNull();
  });

  it('returns an empty string when daysUntilVerificationDeadline is null', () => {
    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        userVerificationDeadline: null,
      },
    });

    expect(wrapper.vm.bannerColor).toBe('');
    expect(wrapper.vm.dependentsStatusMessage).toBeNull();
  });

  it('returns "bg-secondary" when daysUntilVerificationDeadline is greater than 7', () => {
    const userVerificationDeadline = new Date('2024-01-30');
    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        userVerificationDeadline,
      },
    });

    expect(wrapper.vm.bannerColor).toBe('bg-secondary');
    ui.formatString(T.dependentsBlockedMessage, {
      days: 10,
    });
  });

  it('returns "bg-danger" when daysUntilVerificationDeadline is less than or equal to 1', () => {
    const userVerificationDeadline = new Date('2024-01-21');
    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        userVerificationDeadline,
      },
    });

    expect(wrapper.vm.bannerColor).toBe('bg-danger');
    expect(wrapper.vm.dependentsStatusMessage).toBe(T.dependentsRedMessage);
  });

  it('returns "bg-warning" when daysUntilVerificationDeadline is between 1 and 7 (inclusive)', () => {
    const userVerificationDeadline = new Date('2024-01-25');
    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        userVerificationDeadline,
      },
    });

    expect(wrapper.vm.bannerColor).toBe('bg-warning');
    ui.formatString(T.dependentsBlockedMessage, {
      days: 5,
    });
  });
});
