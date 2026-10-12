import { mount } from '@vue/test-utils';

import { types } from '../../api_types';
import T from '../../lang';

import problem_SettingsSummary from './SettingsSummary.vue';

const baseSettingsSummaryProps = {
  showVisibilityIndicators: false,
  showEditLink: true,
  problem: {
    accepts_submissions: true,
    accepted: 0,
    allow_user_add_tags: true,
    creation_date: new Date(),
    email_clarifications: true,
    order: 'normal',
    score: 100,
    statement: { images: {}, sources: {}, language: 'es', markdown: '' },
    submissions: 0,
    version: '587cb50672aa364c75e16b638ec7ca7289e24b08',
    visits: 0,
    letter: 'A',
    alias: 'sumas',
    commit: '587cb50672aa364c75e16b638ec7ca7289e24b08',
    input_limit: 10240,
    languages: ['java', 'py2', 'py3'],
    points: 17.38,
    problem_id: 1,
    quality_seal: false,
    settings: {
      cases: { sample: { in: '1 2\n', out: '3\n', weight: 1 } },
      limits: {
        ExtraWallTime: '0s',
        MemoryLimit: 33554432,
        OutputLimit: 10240,
        OverallWallTimeLimit: '1s',
        TimeLimit: '1s',
      },
      validator: { name: 'token-numeric', tolerance: 1e-9 },
    },
    title: 'Sumas',
    visibility: 2,
  } as types.ArenaProblemDetails,
};

describe('SettingsSummary.vue', () => {
  it('Should handle problem settings summary in contest', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: baseSettingsSummaryProps,
    });

    expect(wrapper.text()).toContain(T.wordsInOut);
  });

  it('Should handle problem settings summary out of contest', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        showVisibilityIndicators: true,
      },
    });

    expect(wrapper.text()).not.toContain(T.wordsInOut);
    expect(wrapper.find('td[data-memory-limit]').text()).toContain('32 MiB');
  });

  it('Should handle problem settings summary with memory limit as string', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        problem: {
          settings: { limits: { MemoryLimit: '32 MiB' } },
          languages: ['java', 'py'],
          accepts_submissions: true,
        },
      },
    });

    expect(wrapper.find('td[data-memory-limit]').text()).toContain('32 MiB');
  });

  it('Should handle empty problem settings summary in lectures', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        problem: { languages: [], accepts_submissions: false },
      },
    });

    expect(wrapper.find('table').exists()).toBeFalsy();
  });

  it('Should handle empty problem settings summary in problem', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        problem: { languages: [], accepts_submissions: true },
      },
    });

    expect(wrapper.text()).not.toContain('undefined');
  });

  it('Should hide the bookmark button for logged-out users', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        userLoggedIn: false,
      },
    });

    expect(wrapper.find('button[data-bookmark-button]').exists()).toBeFalsy();
  });

  it('Should hide the bookmark button inside contests or courses', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        userLoggedIn: true,
        inContestOrCourse: true,
      },
    });

    expect(wrapper.find('button[data-bookmark-button]').exists()).toBeFalsy();
  });

  it('Should show the bookmark button with the add title when not bookmarked', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        userLoggedIn: true,
        isBookmarked: false,
      },
    });

    const button = wrapper.find('button[data-bookmark-button]');
    expect(button.exists()).toBeTruthy();
    expect(button.attributes('title')).toBe(T.problemBookmarkAdd);
  });

  it('Should show the remove title when the problem is bookmarked', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        userLoggedIn: true,
        isBookmarked: true,
      },
    });

    const button = wrapper.find('button[data-bookmark-button]');
    expect(button.exists()).toBeTruthy();
    expect(button.attributes('title')).toBe(T.problemBookmarkRemove);
  });

  it('Should emit toggle-bookmark with the problem alias on click', async () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: {
        ...baseSettingsSummaryProps,
        userLoggedIn: true,
        isBookmarked: false,
      },
    });

    await wrapper.find('button[data-bookmark-button]').trigger('click');

    expect(wrapper.emitted('toggle-bookmark')).toEqual([['sumas']]);
  });
});
