import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { messages, types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice, {
  ActiveProblem,
} from '../components/arena/ContestPractice.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestPracticePayload();
  // The hash is of the form `#problems/${alias}`.
  const problemMatch = /#problems\/([^/]+)/.exec(window.location.hash);
  const problemAlias = problemMatch?.[1] ?? null;
  let problem: ActiveProblem | null = null;
  const problemInfo: types.ProblemInfo | null = null;
  if (problemAlias) {
    getProblemDetails(
      {
        contest_alias: payload.contest.alias,
        problem_alias: problemAlias,
        prevent_problemset_open: false,
      },
      (problemInfo: messages.ProblemDetailsResponse) => {
        const currentProblem = payload.problems?.find(
          ({ alias }) => alias == problemInfo.alias,
        );
        problemInfo.title = currentProblem?.text ?? '';
        problem = { alias: problemInfo.alias, runs: problemInfo.runs ?? [] };
        createComponentContestPractice(problem, problemInfo);
      },
      () => {
        ui.dismissNotifications();
        window.location.hash = '#problems';
        createComponentContestPractice(problem, problemInfo);
      },
    );
  } else {
    createComponentContestPractice(problem, problemInfo);
  }

  function createComponentContestPractice(
    problem: ActiveProblem | null,
    problemInfo: types.ProblemInfo | null,
  ): void {
    const contestPractice = new Vue({
      el: '#main-container',
      components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
      data: () => ({
        problemInfo: problemInfo,
        problems: payload.problems as types.NavbarContestProblem[],
        problem: problem,
      }),
      render: function (createElement) {
        return createElement('omegaup-arena-contest-practice', {
          props: {
            contest: payload.contest,
            problems: this.problems,
            problemInfo: this.problemInfo,
            problem: this.problem,
            activeTab: 'problems',
          },
          on: {
            'navigate-to-problem': (source: ActiveProblem) => {
              getProblemDetails(
                {
                  contest_alias: payload.contest.alias,
                  problem_alias: source.alias,
                  prevent_problemset_open: false,
                },
                (problem: messages.ProblemDetailsResponse) => {
                  const currentProblem = payload.problems?.find(
                    ({ alias }) => alias == problem.alias,
                  );
                  problem.title = currentProblem?.text ?? '';
                  contestPractice.problemInfo = problem;
                  source.alias = problem.alias;
                  source.runs = problem.runs ?? [];
                  window.location.hash = `#problems/${source.alias}`;
                },
                () => {
                  ui.dismissNotifications();
                  window.location.hash = '#problems';
                  contestPractice.problem = null;
                },
              );
            },
          },
        });
      },
    });
  }

  function getProblemDetails(
    params: messages.ProblemDetailsRequest,
    cb: (problem: messages.ProblemDetailsResponse) => void,
    cbError: () => void,
  ): void {
    api.Problem.details(params).then(cb).catch(cbError);
  }
});
