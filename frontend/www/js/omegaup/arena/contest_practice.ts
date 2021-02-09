import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice, {
  ActiveProblem,
} from '../components/arena/ContestPractice.vue';

interface NewClarification {
  problem: null | string;
  message: string;
}

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestPracticePayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';
  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problems: payload.problems as types.NavbarProblemsetProblem[],
      problem: null as ActiveProblem | null,
      clarifications: payload.clarifications,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          contestAdmin: payload.contestAdmin,
          problems: this.problems,
          problemInfo: this.problemInfo,
          problem: this.problem,
          clarifications: this.clarifications,
          activeTab,
        },
        on: {
          'navigate-to-problem': (source: ActiveProblem) => {
            api.Problem.details({
              contest_alias: payload.contest.alias,
              problem_alias: source.alias,
              prevent_problemset_open: false,
            })
              .then((problemInfo) => {
                const currentProblem = payload.problems?.find(
                  ({ alias }) => alias == problemInfo.alias,
                );
                problemInfo.title = currentProblem?.text ?? '';
                contestPractice.problemInfo = problemInfo;
                source.alias = problemInfo.alias;
                source.runs = problemInfo.runs ?? [];
                window.location.hash = `#problems/${source.alias}`;
              })
              .catch(() => {
                ui.dismissNotifications();
                window.location.hash = '#problems';
                contestPractice.problem = null;
              });
          },
          'new-clarification': (newClarification: NewClarification) => {
            api.Clarification.create({
              contest_alias: payload.contest.alias,
              problem_alias: newClarification.problem,
              message: newClarification.message,
            })
              .then(refreshClarifications)
              .catch(ui.apiError);

            return false;
          },
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
          },
          'clarification-response': (
            id: number,
            responseText: string,
            isPublic: boolean,
          ) => {
            api.Clarification.update({
              clarification_id: id,
              answer: responseText,
              public: isPublic,
            })
              .then(refreshClarifications)
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  function refreshClarifications() {
    api.Contest.clarifications({
      contest_alias: payload.contest.alias,
      rowcount: 100,
      offset: null,
    })
      .then(time.remoteTimeAdapter)
      .then((data) => {
        contestPractice.clarifications = data.clarifications;
      });
  }

  // The hash is of the form `#problems/${alias}`.
  const problemMatch = /#problems\/([^/]+)/.exec(window.location.hash);
  const problemAlias = problemMatch?.[1] ?? null;
  if (problemAlias) {
    // This needs to be set here and not at the top because it depends
    // on the `navigate-to-problem` callback being invoked, and that is
    // not the case if this is set a priori.
    contestPractice.problem = { alias: problemAlias, runs: [] };
  }

  setInterval(() => {
    refreshClarifications();
  }, 5 * 60 * 1000);
});
