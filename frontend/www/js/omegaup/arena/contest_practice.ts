import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice, {
  ActiveProblem,
} from '../components/arena/ContestPractice.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestPracticePayload();
  const locationHash = window.location.hash.substr(1).split('/');
  let problemInfo: types.ProblemInfo | null = null;
  const problem: ActiveProblem = { runs: [], alias: null };

  const contestPractice = new Vue({
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: <types.ProblemInfo | null>problemInfo,
      problems: <types.NavbarContestProblem[]>payload.problems,
      problem: <ActiveProblem>problem,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          problems: this.problems,
          problemInfo: this.problemInfo,
          problem: this.problem,
        },
        on: {
          'navigate-to-problem': (source: ActiveProblem) => {
            api.Problem.details({
              contest_alias: payload.contest.alias,
              problem_alias: source.alias,
              prevent_problemset_open: false,
            })
              .then((problemExt) => {
                const currentProblem = payload.problems?.find(
                  ({ alias }) => alias == problemExt.alias,
                );
                problemExt.title = currentProblem?.text ?? '';
                contestPractice.problemInfo = problemExt;
                source.alias = problemExt.alias;
                source.runs = problemExt.runs ?? [];
                window.location.hash = `#problems/${source.alias}`;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  const contestPracticeElement = document.querySelector('#main-container');
  if (locationHash.length > 1) {
    api.Problem.details({
      contest_alias: payload.contest.alias,
      problem_alias: locationHash[1],
      prevent_problemset_open: false,
    })
      .then((problemExt) => {
        const currentProblem = payload.problems?.find(
          ({ alias }) => alias == problemExt.alias,
        );
        problemExt.title = currentProblem?.text ?? '';
        problemInfo = problemExt;
        problem.alias = problemExt.alias;
        problem.runs = problemExt.runs ?? [];
        window.location.hash = `#problems/${locationHash[1]}`;
        if (contestPracticeElement) {
          contestPractice.$mount(contestPracticeElement);
        }
      })
      .catch(ui.apiError);
  } else {
    if (contestPracticeElement) {
      contestPractice.$mount(contestPracticeElement);
    }
  }
});
