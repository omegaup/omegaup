jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';
import { SocketStatus } from '../../arena/events_socket';
import T from '../../lang';
import * as time from '../../time';

import arena_Contest from './Contest.vue';
import arena_RunSubmit from './RunSubmitPopup.vue';

describe('Contest.vue', () => {
  beforeAll(() => {
    const div = document.createElement('div');
    div.id = 'root';
    document.body.appendChild(div);
  });

  afterAll(() => {
    const rootDiv = document.getElementById('root');
    if (rootDiv) {
      document.removeChild(rootDiv);
    }
  });

  const currentDate = new Date();
  const futureDate = new Date();
  futureDate.setMinutes(futureDate.getMinutes() + 2);

  const contest: types.ContestPublicDetails = {
    admission_mode: 'public',
    alias: 'omegaUp',
    description: 'hello omegaUp',
    director: 'omegaUpDirector',
    feedback: 'detailed',
    finish_time: futureDate,
    languages: 'py3',
    partial_score: true,
    penalty: 1,
    penalty_calc_policy: 'sum',
    penalty_type: 'contest_start',
    points_decay_factor: 0,
    problemset_id: 1,
    scoreboard: 100,
    show_penalty: true,
    show_all_contestants_at_first_time_in_scoreboard: false,
    show_scoreboard_after: true,
    start_time: currentDate,
    submissions_gap: 1200,
    title: 'hello omegaUp',
  };

  const problemInfo: types.ProblemInfo = {
    alias: 'problemOmegaUp',
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
      creation_date: currentDate,
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
      language: 'es',
      markdown: '# test',
    },
    title: 'Triangulos',
    visibility: 2,
    input_limit: 1000,
  };

  const problems: types.NavbarProblemsetProblem[] = [
    {
      acceptsSubmissions: true,
      alias: 'problemOmegaUp',
      bestScore: 100,
      hasRuns: true,
      maxScore: 100,
      text: 'A. hello problem omegaUp',
    },
    {
      acceptsSubmissions: true,
      alias: 'otherProblemOmegaUp',
      bestScore: 100,
      hasRuns: true,
      maxScore: 100,
      text: 'B. hello other problem omegaUp',
    },
  ];

  it('Should handle a finished contest', async () => {
    const wrapper = mount(arena_Contest, {
      propsData: {
        contest: Object.assign({}, contest, { finish_time: currentDate }),
        problems,
        problemInfo,
      },
    });

    expect(wrapper.find('.alert-warning').text()).toBe(
      T.arenaContestEndedUsePractice,
    );
  });

  it('Should handle details for a problem in a contest', async () => {
    const wrapper = mount(arena_Contest, {
      propsData: {
        contest,
        problems,
        problemInfo,
      },
    });

    expect(wrapper.find('.socket-status').text()).toBe(SocketStatus.Waiting);
    expect(wrapper.find('a[data-problem=problemOmegaUp]').text()).toBe(
      'A. hello problem omegaUp',
    );
    expect(wrapper.find('a[data-problem=otherProblemOmegaUp]').text()).toBe(
      'B. hello other problem omegaUp',
    );
    expect(wrapper.text()).toContain(problemInfo.points);
    expect(wrapper.text()).toContain(time.formatDateLocal(currentDate));

    await wrapper.find('a[data-problem=problemOmegaUp]').trigger('click');
    expect(wrapper.emitted('navigate-to-problem')).toBeDefined();
  });

  it('Should handle a submission', async () => {
    const wrapper = mount(arena_Contest, {
      attachTo: '#root',
      propsData: {
        contest,
        problems,
        problemInfo,
      },
    });

    await wrapper.find('a[data-problem=problemOmegaUp]').trigger('click');
    expect(wrapper.emitted('navigate-to-problem')).toBeDefined();

    await wrapper
      .find('a[href="#problems/problemOmegaUp/new-run"')
      .trigger('click');
    expect(wrapper.find('form[data-run-submit')).toBeTruthy();

    await wrapper
      .find('select[name="language"] option[value="py3"]')
      .setSelected();

    const runSubmitWrapper = wrapper.findComponent(arena_RunSubmit);

    runSubmitWrapper.setData({
      code: 'print(3)',
    });

    await wrapper.find('form button[type="submit"]').trigger('click');
    expect(wrapper.emitted('submit-run')).toBeDefined();

    wrapper.destroy();
  });
});
