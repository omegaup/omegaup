import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice, {
  ActiveProblem,
} from '../components/arena/ContestPractice.vue';
import problem_Details, {
  PopupDisplayed,
} from '../components/problem/Details.vue';
import { myRunsStore } from '../arena/runsStore';
import arena_NewClarification from '../components/arena/NewClarificationPopup.vue';
import JSZip from 'jszip';
import { getOptionsFromLocation } from './location';
import { navigateToProblem } from './navigation';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';
  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problem: null as ActiveProblem | null,
      problems: payload.problems as types.NavbarProblemsetProblem[],
      clarifications: payload.clarifications,
      popupDisplayed: PopupDisplayed.None,
      showNewClarificationPopup: false,
      guid: null as null | string,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          contestAdmin: payload.contestAdmin,
          problems: this.problems,
          users: payload.users,
          problemInfo: this.problemInfo,
          problem: this.problem,
          clarifications: this.clarifications,
          popupDisplayed: this.popupDisplayed,
          showNewClarificationPopup: this.showNewClarificationPopup,
          activeTab,
          guid: this.guid,
        },
        on: {
          'navigate-to-problem': ({ problem, runs }: ActiveProblem) => {
            navigateToProblem({
              problem,
              runs,
              target: contestPractice,
              problems: this.problems,
            });
          },
          'show-run': (source: {
            target: problem_Details;
            request: { guid: string };
          }) => {
            api.Run.details({ run_alias: source.request.guid })
              .then((data) => {
                if (data.show_diff === 'none' || !commonPayload.isAdmin) {
                  source.target.displayRunDetails(source.request.guid, data);
                  return;
                }
                fetch(
                  `/api/run/download/run_alias/${source.request.guid}/show_diff/true/`,
                )
                  .then((response) => {
                    if (!response.ok) {
                      return Promise.reject(new Error(response.statusText));
                    }
                    return Promise.resolve(response.blob());
                  })
                  .then(JSZip.loadAsync)
                  .then((zip: JSZip) => {
                    const result: {
                      cases: string[];
                      promises: Promise<string>[];
                    } = { cases: [], promises: [] };
                    zip.forEach(async (relativePath, zipEntry) => {
                      const pos = relativePath.lastIndexOf('.');
                      const basename = relativePath.substring(0, pos);
                      const extension = relativePath.substring(pos + 1);
                      if (
                        extension !== 'out' ||
                        relativePath.indexOf('/') !== -1
                      ) {
                        return;
                      }
                      if (
                        data.show_diff === 'examples' &&
                        relativePath.indexOf('sample/') === 0
                      ) {
                        return;
                      }
                      result.cases.push(basename);
                      result.promises.push(
                        zip.file(zipEntry.name).async('text'),
                      );
                    });
                    return result;
                  })
                  .then((response) => {
                    Promise.allSettled(response.promises).then((results) => {
                      results.forEach((result: any, index: number) => {
                        if (data.cases[response.cases[index]]) {
                          data.cases[response.cases[index]].contestantOutput =
                            result.value;
                        }
                      });
                    });
                    source.target.displayRunDetails(source.request.guid, data);
                  })
                  .catch(ui.apiError);
              })
              .catch((error) => {
                ui.apiError(error);
                source.target.popupDisplayed = PopupDisplayed.None;
              });
          },
          'change-show-run-location': (request: {
            guid: string;
            alias: string;
          }) => {
            window.location.hash = `#problems/${request.alias}/show-run:${request.guid}/`;
          },
          'submit-run': (
            request: ActiveProblem & { code: string; selectedLanguage: string },
          ) => {
            api.Run.create({
              problem_alias: request.problem.alias,
              language: request.selectedLanguage,
              source: request.code,
            })
              .then((response) => {
                ui.reportEvent('submission', 'submit');

                updateRun({
                  guid: response.guid,
                  submit_delay: response.submit_delay,
                  username: commonPayload.currentUsername,
                  classname: commonPayload.userClassname,
                  country: 'xx',
                  status: 'new',
                  alias: request.problem.alias,
                  time: new Date(),
                  penalty: 0,
                  runtime: 0,
                  memory: 0,
                  verdict: 'JE',
                  score: 0,
                  language: request.selectedLanguage,
                });
              })
              .catch((run) => {
                ui.error(run.error ?? run);
                if (run.errorname) {
                  ui.reportEvent('submission', 'submit-fail', run.errorname);
                }
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
          'reset-hash': (request: { selectedTab: string; alias: string }) => {
            window.location.replace(`#${request.selectedTab}/${request.alias}`);
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

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  Object.assign(contestPractice, getOptionsFromLocation(window.location.hash));

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

  function updateRun(run: types.Run): void {
    trackRun(run);

    // TODO: Implement websocket support

    if (run.status != 'ready') {
      updateRunFallback(run.guid);
      return;
    }
  }

  function updateRunFallback(guid: string): void {
    setTimeout(() => {
      api.Run.status({ run_alias: guid })
        .then(time.remoteTimeAdapter)
        .then((response) => updateRun(response))
        .catch(ui.ignoreError);
    }, 5000);
  }

  function trackRun(run: types.Run): void {
    myRunsStore.commit('addRun', run);
  }

  setInterval(() => {
    refreshClarifications();
  }, 5 * 60 * 1000);
});
