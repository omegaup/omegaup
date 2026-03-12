import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';

import arena_EphemeralGrader from './EphemeralGrader.vue';

describe('EphemeralGrader.vue', () => {
  beforeEach(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
  });

  afterEach(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.removeChild(rootDiv);
    }
  });

  const date = new Date();
  const problem: types.ProblemInfo = {
    alias: 'triangulos',
    accepts_submissions: true,
    karel_problem: false,
    commit: 'abc',
    languages: ['py3'],
    limits: {
      input_limit: '10 KiB',
      memory_limit: '32 MiB',
      overall_wall_time_limit: '1s',
      time_limit: '1s',
    },
    points: 100,
    problem_id: 1,
    problemsetter: {
      classname: 'user-rank-unranked',
      creation_date: date,
      name: 'omegaUp admin',
      username: 'omegaup',
    },
    quality_seal: false,
    sample_input: undefined,
    settings: {
      cases: {
        statement_001: {
          in: '6\n2 3 2 3 2 4',
          out: '10',
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
    source: 'omegaUp classics',
    statement: {
      images: {},
      sources: {},
      language: 'en',
      markdown: `# test with embed code
Here we can add code.
<details>
  <summary>
    Example:
  </summary>

  {{sample.cpp}}

  </details>
      `,
    },
    title: 'Triangulos',
    visibility: 2,
    input_limit: 1000,
  };

  it('Should handle showing the ephemeral grader for a problem upon load', async () => {
    const wrapper = mount(arena_EphemeralGrader, {
      attachTo: '#root',
      propsData: {
        problem,
        acceptedLanguages: ['py3'],
        preferredLanguage: 'py3',
      },
    });
    expect(wrapper.text()).toContain('triangulos');
    expect(wrapper.text()).toContain('in');
    expect(wrapper.text()).toContain('out');
    expect(wrapper.text()).toContain('code');
    expect(wrapper.text()).toContain('diff');
    expect(wrapper.get('[data-run-button]').exists()).toBe(true);
    expect(wrapper.get('option[value="py3"]').exists()).toBe(true);

    wrapper.destroy();
  });

  it('Should handle showing the ephemeral grader for a problem after changing settings', async () => {
    const wrapper = mount(arena_EphemeralGrader, {
      attachTo: '#root',
      propsData: {
        problem,
        acceptedLanguages: ['py3'],
        preferredLanguage: 'py3',
        canSubmit: false,
      },
    });

    const newAlias = 'sumas2';
    await wrapper.setProps({
      problem: {
        ...problem,
        alias: newAlias,
      },
      acceptedLanguages: ['cpp17-gcc'],
      preferredLanguage: 'cpp17-gcc',
      canSubmit: true,
    });

    expect(wrapper.text()).toContain('sumas2');
    expect(wrapper.text()).toContain('in');
    expect(wrapper.text()).toContain('out');
    expect(wrapper.text()).toContain('code');
    expect(wrapper.text()).toContain('diff');
    expect(wrapper.get('[data-run-button]').exists()).toBe(true);
    expect(wrapper.get('[data-submit-button]').exists()).toBe(true);
    expect(wrapper.get('option[value="cpp17-gcc"]').exists()).toBe(true);

    wrapper.destroy();
  });
});
