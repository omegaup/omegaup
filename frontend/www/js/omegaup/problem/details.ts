import Vue from 'vue';
import problem_Details, {
  PopupDisplayed,
} from '../components/problem/Details.vue';
import {
  getOptionsFromLocation,
  getProblemAndRunDetails,
} from '../arena/location';
import qualitynomination_Demotion from '../components/qualitynomination/DemotionPopup.vue';
import qualitynomination_Promotion from '../components/qualitynomination/PromotionPopup.vue';
import { myRunsStore, runsStore, RunFilters } from '../arena/runsStore';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import T from '../lang';
import {
  ContestClarification,
  refreshProblemClarifications,
  trackClarifications,
} from '../arena/clarifications';
import clarificationStore from '../arena/clarificationsStore';
import {
  onRefreshRuns,
  onSetNominationStatus,
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
  trackRun,
  updateRunFallback,
} from '../arena/submissions';

OmegaUp.on('ready', async () => {
  const payload = types.payloadParsers.ProblemDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const locationHash = window.location.hash.substr(1).split('/');
  const runs =
    payload.user.admin && payload.allRuns ? payload.allRuns : payload.runs;

  const { guid, popupDisplayed } = getOptionsFromLocation(window.location.hash);

  const searchResultEmpty: types.ListItem[] = [];
  let runDetails: null | types.RunDetails = null;
  try {
    ({ runDetails } = await getProblemAndRunDetails({
      location: window.location.hash,
    }));
  } catch (e: any) {
    ui.apiError(e);
  }

  trackClarifications(payload.clarifications ?? []);

  let nextSubmissionTimestamp: null | Date = null;
  if (payload.problem.nextSubmissionTimestamp != null) {
    nextSubmissionTimestamp = time.remoteTime(
      payload.problem.nextSubmissionTimestamp.getTime(),
    );
  }

  let nextExecutionTimestamp: null | Date = null;
  if (payload.problem.nextExecutionTimestamp != null) {
    nextExecutionTimestamp = time.remoteTime(
      payload.problem.nextExecutionTimestamp.getTime(),
    );
  }

  const problemDetailsView = new Vue({
    el: '#main-container',
    components: {
      'omegaup-problem-details': problem_Details,
    },
    data: () => ({
      popupDisplayed,
      runDetailsData: runDetails,
      solutionStatus: payload.solutionStatus,
      solution: null as types.ProblemStatement | null,
      allowedSolutionsToSee: payload.allowedSolutionsToSee,
      activeTab: window.location.hash ? locationHash[0] : 'problems',
      nominationStatus: payload.nominationStatus,
      hasBeenNominated:
        payload.nominationStatus?.nominated ||
        (payload.nominationStatus?.nominatedBeforeAc &&
          !payload.nominationStatus?.solved),
      guid,
      nextSubmissionTimestamp,
      nextExecutionTimestamp,
      createdGuid: '',
      searchResultUsers: searchResultEmpty,
      searchResultProblems: searchResultEmpty,
      isBookmarked: payload.isBookmarked,
      isLoadingBookmark: false,
    }),
    render: function (createElement) {
      return createElement('omegaup-problem-details', {
        props: {
          activeTab: this.activeTab,
          allRuns: runsStore.state.runs,
          problem: payload.problem,
          runs: myRunsStore.state.runs,
          solvers: payload.solvers,
          user: payload.user,
          nominationStatus: this.nominationStatus,
          histogram: payload.histogram,
          clarifications: clarificationStore.state.clarifications,
          solutionStatus: this.solutionStatus,
          solution: this.solution,
          allowedSolutionsToSee: this.allowedSolutionsToSee,
          popupDisplayed: this.popupDisplayed,
          runDetailsData: this.runDetailsData,
          allowUserAddTags: payload.allowUserAddTags,
          levelTags: payload.levelTags,
          problemLevel: payload.problemLevel,
          publicTags: payload.publicTags,
          selectedPublicTags: payload.selectedPublicTags,
          selectedPrivateTags: payload.selectedPrivateTags,
          hasBeenNominated: this.hasBeenNominated,
          guid: this.guid,
          isAdmin: payload.user.admin,
          showVisibilityIndicators: true,
          nextSubmissionTimestamp: this.nextSubmissionTimestamp,
          nextExecutionTimestamp: this.nextExecutionTimestamp,
          createdGuid: this.createdGuid,
          shouldShowTabs: true,
          searchResultUsers: this.searchResultUsers,
          searchResultProblems: this.searchResultProblems,
          problemAlias: payload.problem.alias,
          totalRuns: runsStore.state.totalRuns,
          bookmarkedStatus: this.isBookmarked,
        },
        on: {
          'show-run': (request: SubmissionRequest) => {
            api.Run.details({ run_alias: request.guid })
              .then((runDetails) => {
                this.runDetailsData = showSubmission({ request, runDetails });
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
            if (value) {
              runsStore.commit('applyFilter', {
                [filter]: value,
              } as RunFilters);
            } else {
              runsStore.commit('removeFilter', filter);
            }
            refreshRuns();
          },
          'submit-run': ({
            code,
            language,
            nominationStatus,
          }: {
            code: string;
            language: string;
            runs: types.Run[];
            nominationStatus: types.NominationStatus;
          }) => {
            api.Run.create({
              problem_alias: payload.problem.alias,
              language: language,
              source: code,
            })
              .then(time.remoteTimeAdapter)
              .then((response) => {
                problemDetailsView.nextSubmissionTimestamp =
                  response.nextSubmissionTimestamp;
                problemDetailsView.createdGuid = response.guid;

                submitRun({
                  guid: response.guid,
                  submitDelay: response.submit_delay,
                  language,
                  username: commonPayload.currentUsername,
                  classname: commonPayload.userClassname,
                  problemAlias: payload.problem.alias,
                });
                setNominationStatus({
                  runs: myRunsStore.state.runs,
                  nominationStatus,
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
          'rate-problem-as-reviewer': ({
            tags,
            qualitySeal,
          }: {
            tags: string[];
            qualitySeal: boolean;
          }) => {
            const contents: { quality_seal?: boolean; tag?: string } = {};
            if (tags) {
              contents.tag = tags[0];
            }
            contents.quality_seal = qualitySeal;
            api.QualityNomination.create({
              problem_alias: payload.problem.alias,
              nomination: 'quality_tag',
              contents: JSON.stringify(contents),
            }).catch(ui.apiError);
          },
          'submit-demotion': (source: qualitynomination_Demotion) => {
            api.QualityNomination.create({
              problem_alias: payload.problem.alias,
              nomination: 'demotion',
              contents: JSON.stringify({
                rationale: source.rationale || 'N/A',
                reason: source.selectedReason,
                original: source.original,
              }),
            }).catch(ui.apiError);
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
              problem_alias: payload.problem.alias,
              nomination: 'suggestion',
              contents: JSON.stringify(contents),
            })
              .then(() => {
                this.hasBeenNominated = true;
                ui.reportEvent('quality-nomination', 'submit');
                ui.dismissNotifications();
              })
              .catch(ui.apiError);
          },
          'dismiss-promotion': (
            source: qualitynomination_Promotion,
            isDismissed: boolean,
          ) => {
            const contents: { before_ac?: boolean } = {};
            if (!source.solved && source.tried) {
              contents.before_ac = true;
            }
            if (!isDismissed) {
              return;
            }
            api.QualityNomination.create({
              problem_alias: payload.problem.alias,
              nomination: 'dismissal',
              contents: JSON.stringify(contents),
            })
              .then(() => {
                ui.reportEvent('quality-nomination', 'dismiss');
                ui.info(T.qualityNominationRateProblemDesc);
              })
              .catch(ui.apiError);
          },
          'unlock-solution': () => {
            api.Problem.solution(
              {
                problem_alias: payload.problem.alias,
                forfeit_problem: true,
              },
              { quiet: true },
            )
              .then((data) => {
                if (!data.solution) {
                  ui.error(T.wordsProblemOrSolutionNotExist);
                  return;
                }
                this.solutionStatus = 'unlocked';
                this.solution = data.solution;
                ui.info(
                  ui.formatString(T.solutionViewsLeft, {
                    available: this.allowedSolutionsToSee - 1,
                    total: 5,
                  }),
                );
              })
              .catch((error) => {
                if (error.httpStatusCode == 404) {
                  ui.error(T.wordsProblemOrSolutionNotExist);
                  return;
                }
                ui.apiError(error);
              });
          },
          'get-allowed-solutions': () => {
            api.ProblemForfeited.getCounts()
              .then((data) => {
                this.allowedSolutionsToSee = data.allowed - data.seen;
                if (this.allowedSolutionsToSee <= 0) {
                  ui.warning(T.allowedSolutionsLimitReached);
                }
              })
              .catch(ui.apiError);
          },
          'get-solution': () => {
            if (payload.solutionStatus === 'unlocked') {
              api.Problem.solution(
                { problem_alias: payload.problem.alias },
                { quiet: true },
              )
                .then((data) => {
                  if (!data.solution) {
                    ui.error(T.wordsProblemOrSolutionNotExist);
                    return;
                  }
                  this.solution = data.solution;
                })
                .catch((error) => {
                  if (error.httpStatusCode == 404) {
                    ui.error(T.wordsProblemOrSolutionNotExist);
                    return;
                  }
                  ui.apiError(error);
                });
            }
          },
          'clarification-response': ({
            clarification,
          }: ContestClarification) => {
            api.Clarification.update(clarification)
              .then(() => {
                refreshProblemClarifications({
                  problemAlias: payload.problem.alias,
                  rowcount: 20,
                  offset: 0,
                });
              })
              .catch(ui.apiError);
          },
          'update:activeTab': (tabName: string) => {
            history.replaceState({ tabName }, 'updateTab', `#${tabName}`);
          },
          'redirect-login-page': () => {
            window.location.href = `/login/?redirect=${encodeURIComponent(
              window.location.pathname,
            )}`;
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
          'update-search-result-users': ({ query }: { query: string }) => {
            api.User.list({ query })
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
          'update-search-result-problems': (query: string) => {
            api.Problem.listForTypeahead({
              query,
              search_type: 'all',
            })
              .then((data) => {
                this.searchResultProblems = data.results.map(
                  ({ key, value }) => ({
                    key,
                    value,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'toggle-bookmark': (problemAlias: string) => {
            if (this.isLoadingBookmark) {
              return;
            }
            this.isLoadingBookmark = true;
            api.ProblemBookmark.toggle({ problem_alias: problemAlias })
              .then((response) => {
                this.isBookmarked = response.bookmarked;
                ui.success(
                  response.bookmarked
                    ? T.problemBookmarkAdded
                    : T.problemBookmarkRemoved,
                );
              })
              .catch((error) => {
                ui.apiError(error);
              })
              .finally(() => {
                this.isLoadingBookmark = false;
              });
          },
        },
      });
    },
  });

  window.addEventListener(
    'message',
    (e) => {
      if (e.origin != window.location.origin || !e.data) return;
      switch (e.data.method) {
        case 'submitRun':
          api.Run.create(e.data.params)
            .then(time.remoteTimeAdapter)
            .then((response) => {
              problemDetailsView.nextSubmissionTimestamp =
                response.nextSubmissionTimestamp;
              problemDetailsView.createdGuid = response.guid;
              submitRun({
                guid: response.guid,
                submitDelay: response.submit_delay,
                language: e.data.params.language,
                username: commonPayload.currentUsername,
                classname: commonPayload.userClassname,
                problemAlias: payload.problem.alias,
              });
            })
            .catch((run) => {
              submitRunFailed({
                error: run.error,
                errorname: run.errorname,
                run,
              });
            });
          break;
        case 'executeRun':
          api.Run.execute()
            .then(time.remoteTimeAdapter)
            .then((response) => {
              problemDetailsView.nextExecutionTimestamp =
                response.nextExecutionTimestamp;
            })
            .catch((run) => {
              submitRunFailed({
                error: run.error,
                errorname: run.errorname,
                run,
              });
            });
          break;
      }
    },
    false,
  );

  function setNominationStatus({
    runs,
    nominationStatus,
  }: {
    runs: types.Run[];
    nominationStatus: types.NominationStatus;
  }) {
    for (const run of runs) {
      onSetNominationStatus({
        run,
        nominationStatus,
      });
    }
  }

  function refreshRuns(): void {
    api.Problem.runs({
      problem_alias: payload.problem.alias,
      show_all: true,
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
        if (!problemDetailsView.nominationStatus) return;
        onRefreshRuns({ runs: response.runs, totalRuns: response.totalRuns });
        setNominationStatus({
          runs: response.runs,
          nominationStatus: problemDetailsView.nominationStatus,
        });
      })
      .catch(ui.apiError);
  }

  if (runs) {
    runsStore.commit('setTotalRuns', payload.totalRuns);
    for (const run of runs) {
      trackRun({ run });
    }
    if (problemDetailsView.nominationStatus) {
      setNominationStatus({
        runs: myRunsStore.state.runs,
        nominationStatus: problemDetailsView.nominationStatus,
      });
    }
  }
  if (payload.user.admin) {
    setInterval(() => {
      refreshRuns();
      refreshProblemClarifications({
        problemAlias: payload.problem.alias,
        rowcount: 20,
        offset: 0,
      });
    }, 5 * 60 * 1000);
  }
  if (locationHash.includes('new-run')) {
    problemDetailsView.popupDisplayed = PopupDisplayed.RunSubmit;
  } else if (locationHash[1] && locationHash[1].includes('show-run:')) {
    const showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    const showRunMatch = window.location.hash.match(showRunRegex);
    problemDetailsView.guid = showRunMatch ? showRunMatch[1] : null;
    problemDetailsView.popupDisplayed = PopupDisplayed.RunDetails;
  } else if (
    (payload.nominationStatus?.solved || payload.nominationStatus?.tried) &&
    !(
      payload.nominationStatus?.dismissed ||
      (payload.nominationStatus?.dismissedBeforeAc &&
        !payload.nominationStatus?.solved)
    ) &&
    !(
      payload.nominationStatus?.nominated ||
      (payload.nominationStatus?.nominatedBeforeAc &&
        !payload.nominationStatus?.solved)
    ) &&
    payload.nominationStatus?.canNominateProblem
  ) {
    problemDetailsView.popupDisplayed = PopupDisplayed.Promotion;
  }
});
