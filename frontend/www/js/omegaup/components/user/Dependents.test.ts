import { shallowMount } from '@vue/test-utils';
import user_Dependents from './Dependents.vue';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';

describe('Dependents.vue', () => {
  const now = new Date('2024-01-20');
  let dateNowSpy: jest.SpyInstance<number, []> | null = null;
  const dependents: types.UserDependent[] = [
    {
      username: 'omegaup',
      name: 'omegaup',
      classname: 'user-rank-unranked',
      parent_email_verification_deadline: new Date('2024-01-31'),
    },
  ];

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

  it('Displays correctly with the verification deadline message when user has more than 7 days to verify their child account', () => {
    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        dependents,
      },
    });

    const expectedDaysDifference = 11;
    expect(wrapper.find('tbody>tr>td>small').text()).toBe(
      ui.formatString(T.dependentsMessage, {
        days: expectedDaysDifference,
      }),
    );
    expect(
      wrapper.find('omegaup-user-username-stub').attributes('username'),
    ).toBe('omegaup');
    expect(
      wrapper.find('table tbody tr > td:nth-child(3)').attributes('class'),
    ).toContain('background-warning');
  });

  it('Displays correctly with the verification deadline message when user has only 1 day to verify their child account', () => {
    dependents[0].parent_email_verification_deadline = new Date('2024-01-21');

    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        dependents,
      },
    });

    expect(wrapper.find('tbody>tr>td>small').text()).toBe(
      T.dependentsOneDayUntilVerificationDeadline,
    );
    expect(
      wrapper.find('omegaup-user-username-stub').attributes('username'),
    ).toBe('omegaup');
    expect(
      wrapper.find('table tbody tr > td:nth-child(3)').attributes('class'),
    ).toContain('background-danger');
  });

  it("Displays correctly with the verification deadline message when their user's child account is blocked", () => {
    dependents[0].parent_email_verification_deadline = new Date('2024-01-19');

    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        dependents,
      },
    });

    expect(wrapper.find('tbody>tr>td>small').text()).toBe(
      T.dependentsBlockedMessage,
    );
    expect(
      wrapper.find('omegaup-user-username-stub').attributes('username'),
    ).toBe('omegaup');
    expect(
      wrapper.find('table tbody tr > td:nth-child(3)').attributes('class'),
    ).toContain('background-secondary');
  });

  it('Displays correctly with the verification deadline message when user has already verified thier child account', () => {
    dependents[0].parent_verified = true;

    const wrapper = shallowMount(user_Dependents, {
      propsData: {
        dependents,
      },
    });

    expect(wrapper.find('tbody>tr>td>small').text()).toBe(T.dependentsVerified);
    expect(
      wrapper.find('omegaup-user-username-stub').attributes('username'),
    ).toBe('omegaup');
    expect(
      wrapper.find('table tbody tr > td:nth-child(3)').attributes('class'),
    ).toContain('background-success');
  });
});
