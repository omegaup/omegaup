import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import arena_NewClarificationPopup from './NewClarificationPopup.vue';

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
        ] as types.NavbarProblemsetProblem[],
        users: null,
        problem: 'sumas',
        message: 'new clarification',
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
        ] as types.NavbarProblemsetProblem[],
        users: [
          {
            access_time: undefined,
            country_id: undefined,
            end_time: new Date(),
            is_owner: undefined,
            username: 'omegaUp',
          },
        ] as types.ContestUser[],
        problem: 'sumas',
        username: 'omegaUp',
        message: 'new clarification',
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
