import { shallowMount } from '@vue/test-utils';
import user_Dependents from './Dependents.vue';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';

describe('Dependents.vue', () => {
  const now = new Date('2024-01-20');
  let dateNowSpy: jest.SpyInstance<number, []> | null = null;
  const dependent: types.UserDependent = {
    username: 'omegaup',
    name: 'omegaup',
    classname: 'user-rank-unranked',
    parent_email_verification_deadline: new Date('2024-01-31'),
  };

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

  const userVerificationMapping: {
    dependent: types.UserDependent;
    expectedBackgroundClass: string;
    expectedMessage: string;
    description: string;
  }[] = [
    {
      dependent,
      expectedBackgroundClass: 'background-warning',
      expectedMessage: ui.formatString(T.dependentsMessage, {
        days: 11,
      }),
      description: 'user has more than 7 days to verify their child account',
    },
    {
      dependent: {
        ...dependent,
        parent_email_verification_deadline: new Date('2024-01-21'),
      },
      expectedBackgroundClass: 'background-danger',
      expectedMessage: T.dependentsOneDayUntilVerificationDeadline,
      description: 'user has only 1 day to verify their child account',
    },
    {
      dependent: {
        ...dependent,
        parent_email_verification_deadline: new Date('2024-01-19'),
      },
      expectedBackgroundClass: 'background-secondary',
      expectedMessage: T.dependentsBlockedMessage,
      description: "their user's child account is blocked",
    },
    {
      dependent: {
        ...dependent,
        parent_verified: true,
        parent_email_verification_deadline: undefined,
      },
      expectedBackgroundClass: 'background-success',
      expectedMessage: T.dependentsVerified,
      description: 'user has already verified thier child account',
    },
  ];

  describe.each(userVerificationMapping)(
    `Displays correctly with the verification deadline message when:`,
    (user) => {
      it(user.description, () => {
        const wrapper = shallowMount(user_Dependents, {
          propsData: {
            dependents: [user.dependent],
          },
        });

        expect(wrapper.find('tbody>tr>td>small').text()).toBe(
          user.expectedMessage,
        );
        expect(
          wrapper.find('omegaup-user-username-stub').attributes('username'),
        ).toBe('omegaup');
        expect(
          wrapper.find('table tbody tr > td:nth-child(3)').attributes('class'),
        ).toContain(user.expectedBackgroundClass);
      });
    },
  );
});
