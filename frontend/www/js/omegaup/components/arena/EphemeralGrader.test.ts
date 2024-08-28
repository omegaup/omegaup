import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';

import arena_EphemeralGrader from './EphemeralGrader.vue';

type SetSettingsMessage = {
  method: 'setSettings';
  params: {
    problem: types.ProblemInfo;
    languages: string[];
  };
};

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

  xit('Should handle showing the ephemeral grader for a problem upon load', async () => {
    const wrapper = mount(arena_EphemeralGrader, {
      attachTo: '#root',
      propsData: {
        problem,
      },
    });
    const contentWindow: Window = (wrapper.findComponent({ ref: 'grader' })
      .element as HTMLIFrameElement).contentWindow as Window;

    const postPromise = new Promise<SetSettingsMessage>((accept) => {
      contentWindow.postMessage = jest.fn(accept);
    });
    const settingsMessage = await postPromise;
    expect({
      method: settingsMessage.method,
      params: { problem: settingsMessage.params.problem },
    }).toEqual({
      method: 'setSettings',
      params: {
        problem,
      },
    });

    wrapper.destroy();
  });

  xit('Should handle showing the ephemeral grader for a problem after changing settings', async () => {
    const wrapper = mount(arena_EphemeralGrader, {
      attachTo: '#root',
      propsData: {
        problem,
      },
    });
    const contentWindow: Window = (wrapper.findComponent({ ref: 'grader' })
      .element as HTMLIFrameElement).contentWindow as Window;

    const postPromise = new Promise<SetSettingsMessage>((accept) => {
      contentWindow.postMessage = jest.fn(accept);
    });
    const newAlias = 'sumas2';
    await wrapper.setProps({
      problem: {
        ...problem,
        alias: newAlias,
      },
    });
    const settingsMessage = await postPromise;
    expect({
      method: settingsMessage.method,
      params: {
        problem: settingsMessage.params.problem,
        languages: settingsMessage.params.languages,
      },
    }).toEqual({
      method: 'setSettings',
      params: {
        problem: {
          ...problem,
          alias: newAlias,
        },
        languages: [],
      },
    });

    wrapper.destroy();
  });
});
