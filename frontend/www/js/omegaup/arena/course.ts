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
  onRefreshRuns,
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
  trackRun,
  updateRunFallback,
} from './submissions';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { navigateToProblem, NavigationType, ScoreMode } from './navigation';
import {
  CourseClarificationType,
  refreshCourseClarifications,
  trackClarifications,
} from './clarifications';
import { EventsSocket } from './events_socket';
import clarificationStore from './clarificationsStore';
import socketStore from './socketStore';
import { myRunsStore, RunFilters, runsStore } from './runsStore';
import {
  ArenaCourseFeedback,
  FeedbackStatus,
} from '../components/arena/Feedback.vue';

OmegaUp.on('ready', async () => {
  time.setSugarLocale();

  const commonPayload = types.payloadParsers.CommonPayload();
  const payload = types.payloadParsers.AssignmentDetailsPayload();
  const [locationHash] = window.location.hash.substring(1).split('/');

  const courseAdmin = Boolean(
    payload.courseDetails.is_admin ||
      payload.courseDetails.is_curator ||
      payload.courseDetails.is_teaching_assistant,
  );
  const activeTab = getSelectedValidTab(
    locationHash,
    courseAdmin,
    payload.showRanking,
  );

  if (activeTab !== locationHash) {
    window.location.hash = activeTab;
  }
  const {
    guid,
    popupDisplayed,
    problem,
    problemAlias,
    showNewClarificationPopup,
  } = getOptionsFromLocation(window.location.hash);
  let runDetails: null | types.RunDetails = null;
  let problemDetails: null | types.ProblemDetails = null;
  let feedbackMap: Map<number, ArenaCourseFeedback> = new Map();
  let feedbackThreadMap: Map<number, ArenaCourseFeedback> = new Map();
  try {
    ({ runDetails, problemDetails } = await getProblemAndRunDetails({
      problems: payload.currentAssignment.problems,
      location: window.location.hash,
      problemsetId: payload.currentAssignment.problemset_id,
    }));
    if (runDetails != null) {
      ({ feedbackMap, feedbackThreadMap } = getFeedbackMap(
        runDetails.feedback,
      ));
    }
  } catch (e: any) {
    ui.apiError(e);
  }

  trackClarifications(payload.courseDetails.clarifications);

  let nextSubmissionTimestamp: null | Date = null;
  if (problemDetails?.nextSubmissionTimestamp != null) {
    nextSubmissionTimestamp = time.remoteTime(
      problemDetails?.nextSubmissionTimestamp.getTime(),
    );
  }

  let nextExecutionTimestamp: null | Date = null;
  if (problemDetails?.nextExecutionTimestamp != null) {
    nextExecutionTimestamp = time.remoteTime(
      problemDetails?.nextExecutionTimestamp.getTime(),
    );
  }

  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => ({
      problemInfo: problemDetails,
      problem,
      problems: payload.currentAssignment.problems,
      popupDisplayed,
      showNewClarificationPopup,
      guid,
      problemAlias,
      searchResultUsers: [] as types.ListItem[],
      runDetailsData: runDetails,
      nextSubmissionTimestamp,
      nextExecutionTimestamp,
      shouldShowFirstAssociatedIdentityRunWarning:
        payload.shouldShowFirstAssociatedIdentityRunWarning,
      feedbackMap,
      feedbackThreadMap,
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
          totalRuns: runsStore.state.totalRuns,
          searchResultUsers: this.searchResultUsers,
          runDetailsData: this.runDetailsData,
          nextSubmissionTimestamp: this.nextSubmissionTimestamp,
          nextExecutionTimestamp: this.nextExecutionTimestamp,
          socketStatus: socketStore.state.socketStatus,
          shouldShowFirstAssociatedIdentityRunWarning:
            this.shouldShowFirstAssociatedIdentityRunWarning,
          feedbackMap: this.feedbackMap,
          feedbackThreadMap: this.feedbackThreadMap,
          currentUsername: commonPayload.currentUsername,
          currentUserClassName: commonPayload.userClassname,
        },
        on: {
          'navigate-to-assignment': ({
            assignmentAliasToShow,
            courseAlias,
          }: {
            assignmentAliasToShow: string;
            courseAlias: string;
          }) => {
            window.location.href = `/course/${courseAlias}/assignment/${assignmentAliasToShow}/`;
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
          'update-search-result-users-assignment': ({
            query,
          }: {
            query: string;
          }) => {
            api.Course.searchUsers({
              query,
              course_alias: payload.courseDetails.alias,
              assignment_alias: payload.currentAssignment.alias,
            })
              .then(({ results }) => {
                this.searchResultUsers = results.map(
                  ({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'request-feedback': (guid: string) => {
            if (!window.confirm(T.requestFeedbackConfirm)) {
              return;
            }
            api.Course.requestFeedback({
              course_alias: payload.courseDetails.alias,
              assignment_alias: payload.currentAssignment.alias,
              guid,
            })
              .then(() => {
                ui.success(T.requestFeedback);
              })
              .catch(ui.apiError);
          },
          'show-run': (request: SubmissionRequest) => {
            api.Run.details({ run_alias: request.guid })
              .then((runDetails) => {
                this.runDetailsData = showSubmission({ request, runDetails });

                ({ feedbackMap, feedbackThreadMap } = getFeedbackMap(
                  this.runDetailsData.feedback,
                ));
                this.feedbackMap = feedbackMap;
                this.feedbackThreadMap = feedbackThreadMap;

                if (request.hash) {
                  window.location.hash = request.hash;
                }
              })
              .catch((run) => {
                submitRunFailed({
                  error: run.error,
                  errorname: run.errorname,
                  run,
                });
              });
          },
          'execute-run': ({
            target,
          }: {
            target: Vue & { currentNextExecutionTimestamp: Date };
          }) => {
            api.Run.execute()
              .then(time.remoteTimeAdapter)
              .then((response) => {
                target.currentNextExecutionTimestamp =
                  response.nextExecutionTimestamp;
              })
              .catch(ui.apiError);
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
            history.replaceState({ tabName }, 'updateTab', `#${tabName}`);
          },
          rejudge: (run: types.Run) => {
            api.Run.rejudge({ run_alias: run.guid, debug: false })
              .then(() => {
                run.status = 'rejudging';
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          requalify: (run: types.Run) => {
            api.Run.requalify({ run_alias: run.guid })
              .then(() => {
                run.type = 'normal';
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          'apply-filter': ({
            filter,
            value,
          }: {
            filter:
              | 'verdict'
              | 'language'
              | 'username'
              | 'status'
              | 'offset'
              | 'execution'
              | 'output';
            value: string;
          }) => {
            if (value != '') {
              const newFilter: RunFilters = { [filter]: value };
              runsStore.commit('applyFilter', newFilter);
            } else {
              runsStore.commit('removeFilter', filter);
            }
            refreshRuns();
          },
          disqualify: ({ run }: { run: types.Run }) => {
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
          'reset-hash': ({
            selectedTab,
            alias,
          }: {
            selectedTab: string;
            alias: null | string;
          }) => {
            resetHash(selectedTab, alias);
          },
          'submit-promotion': ({
            solved,
            tried,
            quality,
            difficulty,
          }: {
            solved: boolean;
            tried: boolean;
            quality: string;
            difficulty: string;
          }) => {
            const contents: {
              before_ac?: boolean;
              difficulty?: number;
              quality?: number;
            } = {};
            if (!solved && tried) {
              contents.before_ac = true;
            }
            if (difficulty !== '') {
              contents.difficulty = Number.parseInt(difficulty, 10);
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
                component.currentPopupDisplayed = PopupDisplayed.None;
                ui.reportEvent('quality-nomination', 'submit');
                ui.dismissNotifications();
              })
              .catch(ui.apiError);
          },
          'dismiss-promotion': ({
            solved,
            tried,
            isDismissed,
          }: {
            solved: boolean;
            tried: boolean;
            isDismissed: boolean;
          }) => {
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
                component.currentPopupDisplayed = PopupDisplayed.None;
                ui.success(
                  isUpdate
                    ? T.feedbackSuccessfullyUpdated
                    : T.feedbackSuccessfullyAdded,
                );
              })
              .catch(ui.error);
          },
          'new-submission-popup-displayed': () => {
            if (this.shouldShowFirstAssociatedIdentityRunWarning) {
              this.shouldShowFirstAssociatedIdentityRunWarning = false;
              ui.warning(T.firstSumbissionWithIdentity);
            }
          },
          'save-feedback-list': ({
            feedbackList,
            guid,
          }: {
            feedbackList: { lineNumber: number; feedback: string }[];
            guid: string;
          }) => {
            api.Submission.setFeedbackList({
              guid,
              course_alias: payload.courseDetails.alias,
              assignment_alias: payload.currentAssignment.alias,
              feedback_list: JSON.stringify(feedbackList),
            })
              .then(() => {
                ui.success(T.feedbackSuccessfullyAdded);
                resetHash('runs', null);
                api.Run.getSubmissionFeedback({
                  run_alias: guid,
                }).then((response) => {
                  component.feedbackMap.forEach((feedback) => {
                    feedback.submissionFeedbackId = response.find(
                      (fb) => fb.range_bytes_start == feedback.lineNumber,
                    )?.submission_feedback_id;
                    feedback.status = FeedbackStatus.Saved;
                  });
                });

                component.currentPopupDisplayed = PopupDisplayed.None;
              })
              .catch(ui.apiError);
          },
          'submit-feedback-thread': ({
            feedback,
            guid,
          }: {
            feedback: ArenaCourseFeedback;
            guid: string;
          }) => {
            api.Submission.setFeedback({
              guid,
              course_alias: payload.courseDetails.alias,
              assignment_alias: payload.currentAssignment.alias,
              feedback: feedback.text,
              range_bytes_start: feedback.lineNumber,
              submission_feedback_id: feedback.submissionFeedbackId,
            })
              .then(({ submissionFeedbackThread }) => {
                ui.success(T.feedbackSuccessfullyAdded);
                resetHash('runs', null);
                if (
                  submissionFeedbackThread &&
                  submissionFeedbackThread.submission_feedback_thread_id &&
                  submissionFeedbackThread.identity_id
                ) {
                  feedbackThreadMap.set(
                    submissionFeedbackThread.submission_feedback_thread_id,
                    {
                      author: commonPayload.currentUsername,
                      authorClassname: commonPayload.userClassname,
                      lineNumber: feedback.lineNumber,
                      text: feedback.text,
                      status: FeedbackStatus.Saved,
                    },
                  );
                }
                component.currentPopupDisplayed = PopupDisplayed.None;
              })
              .catch(ui.error);
          },
        },
        ref: 'component',
      });
    },
  });

  function getSelectedValidTab(
    tab: string,
    isAdmin: boolean,
    showRanking: boolean,
  ): string {
    const validTabs = ['problems', 'clarifications'];
    if (showRanking) {
      validTabs.push('ranking');
    }
    if (isAdmin) {
      validTabs.push('runs');
    }
    return validTabs.includes(tab) ? tab : validTabs[0];
  }

  function refreshRuns(): void {
    api.Course.runs({
      course_alias: payload.courseDetails.alias,
      assignment_alias: payload.currentAssignment.alias,
      problem_alias: runsStore.state.filters?.problem,
      offset: runsStore.state.filters?.offset,
      rowcount: runsStore.state.filters?.rowcount,
      verdict: runsStore.state.filters?.verdict,
      language: runsStore.state.filters?.language,
      username: runsStore.state.filters?.username,
      status: runsStore.state.filters?.status,
      execution: runsStore.state.filters?.execution,
      output: runsStore.state.filters?.output,
    })
      .then(time.remoteTimeAdapter)
      .then((response) => {
        onRefreshRuns({ runs: response.runs, totalRuns: response.totalRuns });
      })
      .catch(ui.apiError);
  }

  function getFeedbackMap(runDetailsFeedback: types.SubmissionFeedback[]): {
    feedbackMap: Map<number, ArenaCourseFeedback>;
    feedbackThreadMap: Map<number, ArenaCourseFeedback>;
  } {
    const feedbackMap: Map<number, ArenaCourseFeedback> = new Map();
    const feedbackThreadMap: Map<number, ArenaCourseFeedback> = new Map();

    runDetailsFeedback
      .filter((feedback) => feedback.range_bytes_start != null)
      .map((feedback) => {
        const lineNumber = feedback.range_bytes_start ?? null;
        if (lineNumber != null) {
          feedbackMap.set(lineNumber, {
            author: feedback.author,
            authorClassname: feedback.author_classname,
            lineNumber,
            text: feedback.feedback,
            status: FeedbackStatus.Saved,
            timestamp: feedback.date,
            submissionFeedbackId: feedback.submission_feedback_id,
          });
          feedback.feedback_thread?.map((feedbackThread) => {
            feedbackThreadMap.set(
              feedbackThread.submission_feedback_thread_id,
              {
                author: feedbackThread.author,
                authorClassname: feedbackThread.authorClassname,
                lineNumber,
                text: feedbackThread.text,
                status: FeedbackStatus.Saved,
                timestamp: feedbackThread.timestamp,
              },
            );
          });
        }
      });
    return { feedbackMap, feedbackThreadMap };
  }

  // This function updates the state and URL of the history object in the
  // browser based on the provided parameters
  function resetHash(selectedTab: string, alias: null | string) {
    if (!alias) {
      history.replaceState({ selectedTab }, 'updateTab', `#${selectedTab}`);
      return;
    }
    history.replaceState(
      { selectedTab, alias },
      'resetHash',
      `#${selectedTab}/${alias}`,
    );
  }

  if (payload.currentAssignment.runs) {
    runsStore.commit('setTotalRuns', payload.currentAssignment.totalRuns);
    for (const run of payload.currentAssignment.runs) {
      trackRun({ run });
    }
  }

  const socket = new EventsSocket({
    disableSockets: false,
    problemsetAlias: payload.courseDetails.alias,
    isVirtual: false,
    startTime: payload.currentAssignment.start_time,
    finishTime: payload.currentAssignment.finish_time,
    locationProtocol: window.location.protocol,
    locationHost: window.location.host,
    problemsetId: payload.currentAssignment.problemset_id,
    scoreboardToken: null,
    clarificationsOffset: 1,
    clarificationsRowcount: 30,
    navbarProblems: arenaCourse.problems,
    currentUsername: commonPayload.currentUsername,
    intervalInMilliseconds: 5 * 60 * 1000,
    scoreMode: ScoreMode.Partial,
  });
  socket.connect();

  setInterval(() => {
    refreshCourseClarifications({
      type: CourseClarificationType.AllProblems,
      courseAlias: payload.courseDetails.alias,
    });
  }, 5 * 60 * 1000);

  const component = arenaCourse.$refs.component as arena_Course;

  window.addEventListener('hashchange', async () => {
    const { problem, guid } = getOptionsFromLocation(window.location.hash);
    if (guid != null && problem != null) {
      navigateToProblem({
        type: NavigationType.ForSingleProblemOrCourse,
        problem,
        target: arenaCourse,
        problems: arenaCourse.problems,
        problemsetId: payload.currentAssignment.problemset_id,
        guid,
      });
      component.activeProblem = problem;

      const hash = `#problems/${problem.alias}/show-run:${guid}`;
      api.Run.details({ run_alias: guid })
        .then((runDetails) => {
          arenaCourse.runDetailsData = showSubmission({
            request: {
              guid,
              isAdmin: courseAdmin,
              hash,
            },
            runDetails,
          });

          ({ feedbackMap, feedbackThreadMap } = getFeedbackMap(
            arenaCourse.runDetailsData.feedback,
          ));

          arenaCourse.feedbackMap = feedbackMap;
          arenaCourse.feedbackThreadMap = feedbackThreadMap;

          if (hash) {
            window.location.hash = hash;
          }
        })
        .catch((run) => {
          submitRunFailed({
            error: run.error,
            errorname: run.errorname,
            run,
          });
        });
      component.currentPopupDisplayed = PopupDisplayed.RunDetails;
    }
  });
});
