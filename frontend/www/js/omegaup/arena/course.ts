import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as time from '../time';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

import Vue from 'vue';
import arena_Course from '../components/arena/Course.vue';
import { getOptionsFromLocation, getProblemAndRunDetails } from './location';
import {
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
  trackRun,
  updateRunFallback,
} from './submissions';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { navigateToProblem, NavigationType } from './navigation';
import {
  CourseClarificationType,
  refreshCourseClarifications,
  trackClarifications,
} from './clarifications';
import { EventsSocket } from './events_socket';
import clarificationStore from './clarificationsStore';
import socketStore from './socketStore';
import { myRunsStore, runsStore } from './runsStore';

OmegaUp.on('ready', async () => {
  time.setSugarLocale();

  const commonPayload = types.payloadParsers.CommonPayload();
  const payload = types.payloadParsers.AssignmentDetailsPayload();
  const locationHash = window.location.hash.substr(1).split('/');
  const courseAdmin = Boolean(
    payload.courseDetails.is_admin || payload.courseDetails.is_curator,
  );
  const activeTab = getSelectedValidTab(locationHash[0], courseAdmin);
  if (activeTab !== locationHash[0]) {
    window.location.hash = activeTab;
  }
  let runDetails: null | types.RunDetails = null;
  let problemDetails: null | types.ProblemDetails = null;
  try {
    ({ runDetails, problemDetails } = await getProblemAndRunDetails({
      problems: payload.currentAssignment.problems,
      location: window.location.hash,
    }));
  } catch (e) {
    ui.apiError(e);
  }

  trackClarifications(payload.courseDetails.clarifications);

  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => ({
      popupDisplayed: PopupDisplayed.None,
      problemInfo: null as types.ProblemInfo | null,
      problem: null as types.NavbarProblemsetProblem | null,
      problems: payload.currentAssignment.problems,
      showNewClarificationPopup: false,
      guid: null as null | string,
      problemAlias: null as null | string,
      searchResultUsers: [] as types.ListItem[],
      runDetailsData: runDetails,
      nextSubmissionTimestamp: problemDetails?.nextSubmissionTimestamp,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          clarifications: clarificationStore.state.clarifications,
          course: payload.courseDetails,
          currentAssignment: payload.currentAssignment,
          problemInfo: this.problemInfo,
          problem: this.problem,
          problemAlias: this.problemAlias,
          problems: this.problems,
          popupDisplayed: this.popupDisplayed,
          scoreboard: payload.scoreboard,
          showNewClarificationPopup: this.showNewClarificationPopup,
          showRanking: payload.showRanking,
          activeTab,
          guid: this.guid,
          runs: myRunsStore.state.runs,
          allRuns: runsStore.state.runs,
          searchResultUsers: this.searchResultUsers,
          runDetailsData: this.runDetailsData,
          nextSubmissionTimestamp: this.nextSubmissionTimestamp,
          socketStatus: socketStore.state.socketStatus,
        },
        on: {
          'navigate-to-assignment': ({
            assignmentAliasToShow,
            courseAlias,
            isAdmin,
          }: {
            assignmentAliasToShow: string;
            courseAlias: string;
            isAdmin: boolean;
          }) => {
            window.location.pathname = `/course/${courseAlias}/assignment/${assignmentAliasToShow}/${
              isAdmin ? 'admin/' : ''
            }`;
          },
          'navigate-to-problem': ({
            problem,
          }: {
            problem: types.NavbarProblemsetProblem;
          }) => {
            navigateToProblem({
              type: NavigationType.ForSingleProblemOrCourse,
              problem,
              target: arenaCourse,
              problems: this.problems,
              problemsetId: payload.currentAssignment.problemset_id,
            });
          },
          'show-run': (request: SubmissionRequest) => {
            const hash = `#problems/${
              this.problemAlias ?? request.request.problemAlias
            }/show-run:${request.request.guid}/`;
            api.Run.details({ run_alias: request.request.guid })
              .then((runDetails) => {
                showSubmission({ request, runDetails, hash });
              })
              .catch((error) => {
                ui.apiError(error);
              });
          },
          'submit-run': ({
            problem,
            code,
            language,
          }: {
            code: string;
            language: string;
            problem: types.NavbarProblemsetProblem;
          }) => {
            api.Run.create({
              problemset_id: payload.currentAssignment.problemset_id,
              problem_alias: problem.alias,
              language: language,
              source: code,
            })
              .then(time.remoteTimeAdapter)
              .then((response) => {
                submitRun({
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
          }: {
            clarification: types.Clarification;
            clearForm: () => void;
          }) => {
            if (!clarification) {
              return;
            }
            api.Clarification.create({
              course_alias: payload.courseDetails.alias,
              assignment_alias: payload.currentAssignment.alias,
              problem_alias: clarification.problem_alias,
              username: clarification.author,
              message: clarification.message,
            })
              .then(() => {
                clearForm();
                refreshCourseClarifications({
                  courseAlias: payload.courseDetails.alias,
                  type: CourseClarificationType.AllProblems,
                });
              })
              .catch(ui.apiError);
          },
          'clarification-response': (clarification: types.Clarification) => {
            api.Clarification.update(clarification)
              .then(() => {
                refreshCourseClarifications({
                  courseAlias: payload.courseDetails.alias,
                  type: CourseClarificationType.AllProblems,
                });
              })
              .catch(ui.apiError);
          },
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
          },
          'show-run-all': (request: SubmissionRequest) => {
            const hash = `#runs/show-run:${request.request.guid}/`;
            api.Run.details({ run_alias: request.request.guid })
              .then((runDetails) => {
                showSubmission({ request, runDetails, hash });
              })
              .catch((error) => {
                ui.apiError(error);
              })
              .finally(() => {
                this.popupDisplayed = PopupDisplayed.None;
              });
          },
          rejudge: (run: types.Run) => {
            api.Run.rejudge({ run_alias: run.guid, debug: false })
              .then(() => {
                run.status = 'rejudging';
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          disqualify: (run: types.Run) => {
            if (!window.confirm(T.runDisqualifyConfirm)) {
              return;
            }
            api.Run.disqualify({ run_alias: run.guid })
              .then(() => {
                run.type = 'disqualified';
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          'reset-hash': (request: {
            selectedTab: string;
            alias: null | string;
          }) => {
            if (!request.alias) {
              window.location.replace(`#${request.selectedTab}`);
              return;
            }
            window.location.replace(`#${request.selectedTab}/${request.alias}`);
          },
          'submit-promotion': ({
            solved,
            tried,
            quality,
            difficulty,
            tags,
          }: {
            solved: boolean;
            tried: boolean;
            quality: string;
            difficulty: string;
            tags: string[];
          }) => {
            const contents: {
              before_ac?: boolean;
              difficulty?: number;
              quality?: number;
              tags?: string[];
            } = {};
            if (!solved && tried) {
              contents.before_ac = true;
            }
            if (difficulty !== '') {
              contents.difficulty = Number.parseInt(difficulty, 10);
            }
            if (tags.length > 0) {
              contents.tags = tags;
            }
            if (quality !== '') {
              contents.quality = Number.parseInt(quality, 10);
            }
            api.QualityNomination.create({
              problem_alias: this.problemInfo?.alias,
              nomination: 'suggestion',
              contents: JSON.stringify(contents),
            })
              .then(() => {
                this.popupDisplayed = PopupDisplayed.None;
                ui.reportEvent('quality-nomination', 'submit');
                ui.dismissNotifications();
              })
              .catch(ui.apiError);
          },
          'dismiss-promotion': (
            solved: boolean,
            tried: boolean,
            isDismissed: boolean,
          ) => {
            const contents: { before_ac?: boolean } = {};
            if (!solved && tried) {
              contents.before_ac = true;
            }
            if (!isDismissed) {
              return;
            }
            api.QualityNomination.create({
              problem_alias: this.problemInfo?.alias,
              nomination: 'dismissal',
              contents: JSON.stringify(contents),
            })
              .then(() => {
                ui.reportEvent('quality-nomination', 'dismiss');
                ui.info(T.qualityNominationRateProblemDesc);
              })
              .catch(ui.apiError);
          },
          'set-feedback': ({
            guid,
            feedback,
            isUpdate,
          }: {
            guid: string;
            feedback: string;
            isUpdate: boolean;
          }) => {
            api.Submission.setFeedback({
              guid,
              course_alias: payload.courseDetails.alias,
              assignment_alias: payload.currentAssignment.alias,
              feedback,
            })
              .then(() => {
                this.popupDisplayed = PopupDisplayed.None;
                ui.success(
                  isUpdate
                    ? T.feedbackSuccesfullyUpdated
                    : T.feedbackSuccesfullyAdded,
                );
              })
              .catch(ui.error);
          },
        },
      });
    },
  });

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  Object.assign(arenaCourse, getOptionsFromLocation(window.location.hash));

  function getSelectedValidTab(tab: string, isAdmin: boolean): string {
    const validTabs = ['problems', 'ranking', 'runs', 'clarifications'];
    const defaultTab = 'problems';
    if (tab === 'runs' && !isAdmin) return defaultTab;
    const isValidTab = validTabs.includes(tab);
    return isValidTab ? tab : defaultTab;
  }

  if (payload.currentAssignment.runs) {
    for (const run of payload.currentAssignment.runs) {
      trackRun({ run });
    }
  }

  if (locationHash[1] && locationHash[1].includes('show-run:')) {
    const showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    const showRunMatch = window.location.hash.match(showRunRegex);
    arenaCourse.guid = showRunMatch?.[1] ?? null;
    arenaCourse.popupDisplayed = PopupDisplayed.RunDetails;
  }

  const socket = new EventsSocket({
    disableSockets: false,
    problemsetAlias: payload.courseDetails.alias,
    locationProtocol: window.location.protocol,
    locationHost: window.location.host,
    problemsetId: payload.currentAssignment.problemset_id,
    scoreboardToken: null,
    clarificationsOffset: 1,
    clarificationsRowcount: 30,
    navbarProblems: arenaCourse.problems,
    currentUsername: commonPayload.currentUsername,
    intervalInMilliseconds: 5 * 60 * 1000,
  });
  socket.connect();

  setInterval(() => {
    refreshCourseClarifications({
      type: CourseClarificationType.AllProblems,
      courseAlias: payload.courseDetails.alias,
    });
  }, 5 * 60 * 1000);
});
