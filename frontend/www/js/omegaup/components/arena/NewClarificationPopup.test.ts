import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import arena_NewClarificationPopup, {
  NewClarification,
} from './NewClarificationPopup.vue';

describe('NewClarification.vue', () => {
  it('Should handle empty list of users', () => {
    const wrapper = shallowMount(arena_NewClarificationPopup, {
      propsData: {
        problems: [
          {
            acceptsSubmissions: true,
            alias: 'sumas',
            bestScore: 100,
            hasRuns: true,
            maxScore: 100,
            text: 'A. Sumas',
          },
        ] as types.NavbarContestProblem[],
        users: null,
        newClarification: {
          problem: 'sumas',
          message: null,
        },
      },
    });

    expect(
      wrapper
        .find(
          'form[data-new-clarification] select[data-new-clarification-problem]',
        )
        .text(),
    ).toBe('A. Sumas');
    expect(
      wrapper
        .find(
          'form[data-new-clarification] select[data-new-clarification-user]',
        )
        .exists(),
    ).toBeFalsy();
  });

  it('Should handle list of users', () => {
    const wrapper = shallowMount(arena_NewClarificationPopup, {
      propsData: {
        problems: [
          {
            acceptsSubmissions: true,
            alias: 'sumas',
            bestScore: 100,
            hasRuns: true,
            maxScore: 100,
            text: 'A. Sumas',
          },
        ] as types.NavbarContestProblem[],
        users: [
          {
            access_time: undefined,
            country_id: undefined,
            end_time: new Date(),
            is_owner: undefined,
            username: 'omegaUp',
          },
        ] as types.ContestUser[],
        newClarification: {
          problem: 'sumas',
          message: null,
        } as NewClarification,
      },
    });

    expect(
      wrapper
        .find(
          'form[data-new-clarification] select[data-new-clarification-problem]',
        )
        .text(),
    ).toBe('A. Sumas');
    expect(
      wrapper
        .find(
          'form[data-new-clarification] select[data-new-clarification-user]',
        )
        .text(),
    ).toBe('omegaUp');
  });
});
