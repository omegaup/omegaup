import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import arena_Solvers from './Solvers.vue';
import type { types } from '../../api_types';

describe('Solvers.vue', () => {
  it('Should handle empty solvers list', () => {
    const wrapper = shallowMount(arena_Solvers, {
      propsData: {
        solvers: [] as types.BestSolvers[],
      },
    });

    expect(wrapper.text()).toContain(T.wordsBestSolvers);
  });

  it('Should handle solvers list', async () => {
    const wrapper = shallowMount(arena_Solvers, {
      propsData: {
        solvers: [
          {
            classname: 'user-rank-unranked',
            language: 'py3',
            memory: 20,
            runtime: 10,
            time: new Date(),
            username: 'username',
          },
        ] as types.BestSolvers[],
      },
    });

    expect(wrapper.find('table>caption').text()).toContain(T.wordsBestSolvers);
    expect(wrapper.find('td[data-submission-language]').text()).toContain(
      'py3',
    );
    expect(wrapper.find('td[data-submission-runtime]').text()).toContain(
      (10 / 1000.0).toFixed(2),
    );
    expect(wrapper.find('td[data-submission-memory]').text()).toContain(
      (20 / (1024 * 1024)).toFixed(2),
    );
  });
});
