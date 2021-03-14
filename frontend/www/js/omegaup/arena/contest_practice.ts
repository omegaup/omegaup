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
import JSZip from 'jszip';
import { submitRun, submitRunFailed } from './submissions';
import { getOptionsFromLocation } from './location';
import { navigateToProblem } from './navigation';
import {
  ContestClarification,
  ContestClarificationType,
  ContestClarificationRequest,
  refreshContestClarifications,
  trackClarifications,
} from './clarifications';
import clarificationStore from './clarificationsStore';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';

  trackClarifications(payload.clarifications);

  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problem: null as ActiveProblem | null,
      problems: payload.problems as types.NavbarProblemsetProblem[],
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
          clarifications: clarificationStore.state.clarifications,
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
          'submit-run': ({
            problem,
            runs,
            code,
            language,
          }: ActiveProblem & { code: string; language: string }) => {
            api.Run.create({
              problem_alias: problem.alias,
              language: language,
              source: code,
            })
              .then((response) => {
                submitRun({
                  runs,
                  guid: response.guid,
                  submitDelay: response.submit_delay,
                  language,
                  username: commonPayload.currentUsername,
                  classname: commonPayload.userClassname,
                  problemAlias: problem.alias,
                });
              })
              .catch((run) => {
                submitRunFailed({
                  error: run.error,
                  errorname: run.errorname,
                  run,
                });
              });
          },
          'new-clarification': ({
            clarification,
            clearForm,
            contestClarificationRequest,
          }: {
            clarification: types.Clarification;
            clearForm: () => void;
            contestClarificationRequest: ContestClarificationRequest;
          }) => {
            if (!clarification) {
              return;
            }
            const contestAlias = payload.contest.alias;
            api.Clarification.create({
              contest_alias: contestAlias,
              problem_alias: clarification.problem_alias,
              username: clarification.author,
              message: clarification.message,
            })
              .then(() => {
                clearForm();
                refreshContestClarifications(contestClarificationRequest);
              })
              .catch(ui.apiError);
          },
          'clarification-response': ({
            clarification,
            contestClarificationRequest,
          }: ContestClarification) => {
            api.Clarification.update(clarification)
              .then(() => {
                refreshContestClarifications(contestClarificationRequest);
              })
              .catch(ui.apiError);
          },
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
          },
          'reset-hash': (request: { selectedTab: string; alias: string }) => {
            window.location.replace(`#${request.selectedTab}/${request.alias}`);
          },
        },
      });
    },
  });

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  Object.assign(contestPractice, getOptionsFromLocation(window.location.hash));

  setInterval(() => {
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: payload.contest.alias,
    });
  }, 5 * 60 * 1000);
});
