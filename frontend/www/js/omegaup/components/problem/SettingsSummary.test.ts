import { mount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import { types } from '../../api_types';
import T from '../../lang';
import { omegaup } from '../../omegaup';

import problem_SettingsSummary from './SettingsSummary.vue';

const baseSettingsSummaryProps = {
  inArena: true,
  isAdmin: true,
  problem: {
    alias: 'sumas',
    commit: '587cb50672aa364c75e16b638ec7ca7289e24b08',
    input_limit: 10240,
    languages: ['java', 'py2', 'py3'],
    points: 17.38,
    problem_id: 1,
    quality_seal: false,
    settings: {
      cases: {
        sample: {
          in: '1 2\n',
          out: '3\n',
          weight: 1,
        },
      },
      limits: {
        ExtraWallTime: '0s',
        MemoryLimit: 33554432,
        OutputLimit: 10240,
        OverallWallTimeLimit: '1s',
        TimeLimit: '1s',
      },
      validator: {
        name: 'token-numeric',
        tolerance: 1e-9,
      },
    },
    title: 'Sumas',
    visibility: 2,
  } as omegaup.ArenaProblem,
};

describe('SettingsSummary.vue', () => {
  it('Should handle problem settings summary in contest', () => {
    const wrapper = mount(problem_SettingsSummary, {
      propsData: baseSettingsSummaryProps,
    });

    expect(wrapper.text()).toContain(T.wordsInOut);
  });

  it('Should handle problem settings summary out of contest', () => {
    baseSettingsSummaryProps.inArena = false;
    const wrapper = mount(problem_SettingsSummary, {
      propsData: baseSettingsSummaryProps,
    });

    expect(wrapper.text()).not.toContain(T.wordsInOut);
    expect(wrapper.find('td[data-memory-limit]').text()).toContain('32 MiB');
  });

  it('Should handle problem settings summary with memory limit as string', () => {
    if (baseSettingsSummaryProps.problem.settings) {
      baseSettingsSummaryProps.problem.settings.limits.MemoryLimit = '32 MiB';
    }
    const wrapper = mount(problem_SettingsSummary, {
      propsData: baseSettingsSummaryProps,
    });

    expect(wrapper.find('td[data-memory-limit]').text()).toContain('32 MiB');
  });
});
