import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice, {
  ActiveProblem,
} from '../components/arena/ContestPractice.vue';
import { PopupDisplayed } from '../components/problem/Details.vue';
import arena_NewClarification from '../components/arena/NewClarificationPopup.vue';

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
      problem: null as ActiveProblem | null,
      clarifications: payload.clarifications,
      popupDisplayed: PopupDisplayed.None,
      showNewClarificationPopup: false,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          contestAdmin: payload.contestAdmin,
          problems: payload.problems,
          users: payload.users,
          problemInfo: this.problemInfo,
          problem: this.problem,
          clarifications: this.clarifications,
          popupDisplayed: this.popupDisplayed,
          showNewClarificationPopup: this.showNewClarificationPopup,
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
          'new-clarification': (request: {
            request: types.Clarification;
            target: arena_NewClarification;
          }) => {
            api.Clarification.create({
              contest_alias: payload.contest.alias,
              problem_alias: request.request.problem_alias,
              username: request.request.author,
              message: request.request.message,
            })
              .then(() => {
                request.target.clearForm();
                refreshClarifications();
              })
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

  // The hash is of the forms:
  // - `#problems/${alias}`
  // - `#problems/${alias}/new-run`
  // - `#problems/${alias}/show-run:xyz`
  // - `#clarifications/${alias}/new`
  // and all the matching forms in the following regex
  const match = /#(?<tab>\w+)\/(?<problem>[^/]+)\/?(?<popup>[^/]*)/g.exec(
    window.location.hash,
  );
  if (match?.groups) {
    switch (match?.groups.tab) {
      case 'problems':
        // This needs to be set here and not at the top because it depends
        // on the `navigate-to-problem` callback being invoked, and that is
        // not the case if this is set a priori.
        contestPractice.problem = { alias: match.groups.problem, runs: [] };
        if (match.groups.popup === 'new-run') {
          contestPractice.popupDisplayed = PopupDisplayed.RunSubmit;
        }
        if (match.groups.popup.includes('show-run')) {
          contestPractice.popupDisplayed = PopupDisplayed.RunDetails;
        }
        break;
      case 'clarifications':
        if (match.groups.popup === 'new') {
          contestPractice.showNewClarificationPopup = true;
        }
        break;
    }
  }

  setInterval(() => {
    refreshClarifications();
  }, 5 * 60 * 1000);
});
