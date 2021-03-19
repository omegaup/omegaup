import Vue from 'vue';
import * as Highcharts from 'highcharts/highstock';

import * as api from '../api';
import T from '../lang';
import { omegaup, OmegaUp } from '../omegaup';
import { types, messages } from '../api_types';
import { myRunsStore, runsStore } from './runsStore';
import * as time from '../time';
import * as ui from '../ui';
import JSZip from 'jszip';
import * as markdown from '../markdown';

import arena_ContestSummary from '../components/arena/ContestSummary.vue';
import arena_Navbar_Assignments from '../components/arena/NavbarAssignments.vue';
import arena_Navbar_Miniranking from '../components/arena/NavbarMiniranking.vue';
import arena_Navbar_Problems from '../components/arena/NavbarProblems.vue';
import arena_RunDetails from '../components/arena/RunDetails.vue';
import arena_RunSubmit from '../components/arena/RunSubmit.vue';
import arena_Runs from '../components/arena/Runs.vue';
import arena_Scoreboard from '../components/arena/Scoreboard.vue';
import common_Navbar from '../components/common/Navbar.vue';
import omegaup_Markdown from '../components/Markdown.vue';
import problem_SettingsSummary from '../components/problem/SettingsSummary.vue';
import qualitynomination_Popup from '../components/qualitynomination/Popup.vue';
import { ActiveProblem } from '../components/arena/ContestPractice.vue';

import ArenaAdmin from './admin_arena';

export { ArenaAdmin };

export interface ArenaOptions {
  assignmentAlias: string | null;
  contestAlias: string | null;
  courseAlias: string | null;
  courseName: string | null;
  disableClarifications: boolean;
  disableSockets: boolean;
  isInterview: boolean;
  isLockdownMode: boolean;
  originalContestAlias: string | null;
  originalProblemsetId?: number;
  payload: types.CommonPayload;
  problemsetId: number | null;
  problemsetAdmin: boolean;
  preferredLanguage: string | null;
  scoreboardToken: string | null;
  shouldShowFirstAssociatedIdentityRunWarning: boolean;
  partialScore: boolean;
}

export interface Problem {
  accepts_submissions: boolean;
  alias: string;
  commit: string;
  input_limit: number;
  languages: string[];
  lastSubmission?: Date;
  letter?: string;
  nextSubmissionTimestamp?: Date;
  points: number;
  problemsetter?: types.ProblemsetterInfo;
  quality_seal: boolean;
  quality_payload?: types.ProblemQualityPayload;
  runs?: types.Run[];
  settings?: types.ProblemSettingsDistrib;
  source?: string;
  statement?: types.ProblemStatement;
  title: string;
  visibility: number;
}

// Number of digits after the decimal point to show.
const digitsAfterDecimalPoint: number = 2;

const scoreboardColors = [
  '#FB3F51',
  '#FF5D40',
  '#FFA240',
  '#FFC740',
  '#59EA3A',
  '#37DD6F',
  '#34D0BA',
  '#3AAACF',
  '#8144D6',
  '#CD35D3',
];

function getMaxScore(
  runs: types.Run[],
  alias: string,
  previousScore: number,
): number {
  let maxScore = previousScore;
  for (const run of runs) {
    if (alias != run.alias) {
      continue;
    }
    const score = run.contest_score || 0;
    if (score > maxScore) {
      maxScore = score;
    }
  }
  return maxScore;
}

function findElement(
  element: HTMLElement | null,
  itemSelector: string | null,
): HTMLElement | null {
  if (!element || itemSelector === null) {
    return element;
  }
  return element.querySelector(itemSelector);
}

function setItemText(
  element: HTMLElement | null,
  itemSelector: string | null,
  text: string,
) {
  element = findElement(element, itemSelector);
  if (element) {
    element.textContent = text;
  }
}

export class Arena {
  options: ArenaOptions;

  // Currently opened problem.
  currentProblem: Problem = {
    accepts_submissions: true,
    title: '',
    alias: '',
    commit: '',
    source: '',
    languages: [],
    points: 0,
    input_limit: 0,
    quality_seal: false,
    visibility: 2,
  };

  // The current problemset.
  currentProblemset: types.Problemset | null = null;

  // The interval for clock updates.
  clockInterval: ReturnType<typeof setTimeout> | null = null;

  // The start time of the contest.
  startTime: Date | null = null;

  // The finish time of the contest.
  finishTime: Date | null = null;

  // The deadline for submissions. This might be different from the end time.
  submissionDeadline: Date | null = null;

  // The guid of any run that is pending.
  pendingRuns = {};

  // The set of problems in this contest.
  problems: {
    [alias: string]: Problem;
  } = {};

  // WebSocket for real-time updates.
  socket: EventsSocket | null = null;

  // The offset of each user into the ranking table.
  currentRanking: { [username: string]: number } = {};

  // The previous ranking information. Useful to show diffs.
  prevRankingState: { [username: string]: { place: number } } = {};

  // The previous location hash.
  previousHash: string = '';

  // Every time a recent event is shown, have this interval clear it after
  // 30s.
  removeRecentEventClassTimeout: ReturnType<typeof setTimeout> | null = null;

  // The last known scoreboard event stream.
  currentEvents: types.ScoreboardEvent[] = [];

  // If we have admin powers in this contest.
  problemsetAdmin: boolean = false;

  preferredLanguage: string | null;

  answeredClarifications: number = 0;
  clarificationsOffset: number = 0;
  clarificationsRowcount: number = 20;
  activeTab: string = 'problems';
  clarifications: { [key: number]: HTMLElement } = {};
  submissionGap: number = 0;

  // Clarification refresh interval.
  clarificationInterval: ReturnType<typeof setTimeout> | null = null;

  rankingInterval: ReturnType<typeof setTimeout> | null = null;

  // The interval of time that submissions button will be disabled
  submissionGapInterval: ReturnType<typeof setTimeout> | null = null;

  // Cache scoreboard data for virtual contest
  originalContestScoreboardEvents: types.ScoreboardEvent[] | null = null;

  // Virtual contest refresh interval
  virtualContestRefreshInterval: ReturnType<typeof setTimeout> | null = null;

  // Ephemeral grader support.
  ephemeralGrader: EphemeralGrader = new EphemeralGrader();

  // UI elements
  elements: {
    clarification: HTMLElement | null;
    clock: HTMLElement | null;
    loadingOverlay: HTMLElement | null;
    ranking: HTMLElement | null;
    socketStatus: HTMLElement | null;
  };

  initialClarifications: types.Clarification[] = [];

  navbarAssignments: Vue | null = null;

  navbarProblems:
    | (Vue & {
        problems: types.NavbarProblemsetProblem[];
        activeProblem: string | null;
      })
    | null = null;

  navbarMiniRanking:
    | (Vue & {
        showRanking: boolean;
        users: omegaup.UserRank[];
      })
    | null = null;

  myRunsList: Vue & {
    isContestFinished: boolean;
    isProblemsetOpened: boolean;
    problemAlias: string;
  };

  problemSettingsSummary:
    | (Vue & { problem: types.ArenaProblemDetails })
    | null = null;

  qualityNominationForm:
    | (Vue & { qualityPayload: types.ProblemQualityPayload })
    | null = null;

  commonNavbar:
    | (Vue & {
        initialClarifications: types.Clarification[];
        graderInfo: types.GraderStatus | null;
        errorMessage: string | null;
        graderQueueLength: number;
        notifications: types.Notification[];
      })
    | null = null;

  markdownView: Vue & {
    markdown: string;
    imageMapping: markdown.ImageMapping;
    sourceMapping: markdown.SourceMapping;
    problemSettings?: types.ProblemSettingsDistrib;
  };

  runDetailsView:
    | (Vue & {
        data: types.RunDetails;
      })
    | null = null;

  runSubmitView:
    | (Vue & {
        languages: string[];
        code: string;
        nextSubmissionTimestamp: Date;
        preferredLanguage: string | null;
      })
    | null = null;

  scoreboard:
    | (Vue & {
        problems: omegaup.Problem[];
        ranking: types.ScoreboardRankingEntry[];
        showPenalty: boolean;
        lastUpdated: Date;
      })
    | null = null;

  summaryView: Vue & { contest: omegaup.Contest };

  rankingChart: Highcharts.Chart | null = null;

  constructor(options: ArenaOptions) {
    // eslint-disable-next-line @typescript-eslint/no-this-alias
    const self = this;
    this.options = options;

    // All runs in this contest/problem.
    this.myRunsList = new Vue({
      components: { 'omegaup-arena-runs': arena_Runs },
      data: () => ({
        isContestFinished: false,
        isProblemsetOpened: true,
        problemAlias: null,
      }),
      render: function (createElement) {
        return createElement('omegaup-arena-runs', {
          props: {
            contestAlias: options.contestAlias,
            isContestFinished: this.isContestFinished,
            isProblemsetOpened: this.isProblemsetOpened,
            problemAlias: this.problemAlias,
            runs: myRunsStore.state.runs,
            showDetails: true,
            showPoints: true,
          },
          on: {
            details: (run: types.Run) => {
              window.location.hash += `/show-run:${run.guid}`;
            },
          },
        });
      },
    });
    const myRunsListElement = document.querySelector('#problem table.runs');
    if (myRunsListElement) {
      this.myRunsList.$mount(myRunsListElement);
    }

    // Currently opened clarification notifications.
    if (document.getElementById('common-navbar')) {
      const commonNavbar = (this.commonNavbar = new Vue({
        el: '#common-navbar',
        components: {
          'omegaup-common-navbar': common_Navbar,
        },
        data: () => ({
          graderInfo: null,
          graderQueueLength: -1,
          errorMessage: null,
          initialClarifications: [],
          notifications: [],
        }),
        render: function (createElement) {
          return createElement('omegaup-common-navbar', {
            props: {
              omegaUpLockDown: options.payload.omegaUpLockDown,
              inContest: options.payload.inContest,
              isLoggedIn: options.payload.isLoggedIn,
              isReviewer: options.payload.isReviewer,
              gravatarURL51: options.payload.gravatarURL51,
              gravatarURL128: options.payload.gravatarURL128,
              currentUsername: options.payload.currentUsername,
              isAdmin: options.payload.isAdmin,
              isMainUserIdentity: options.payload.isMainUserIdentity,
              lockDownImage: options.payload.lockDownImage,
              navbarSection: options.payload.navbarSection,
              graderInfo: this.graderInfo,
              graderQueueLength: this.graderQueueLength,
              errorMessage: this.errorMessage,
              initialClarifications: this.initialClarifications,
            },
          });
        },
      }));

      if (this.options.payload.isAdmin) {
        api.Notification.myList({})
          .then((data) => {
            commonNavbar.notifications = data.notifications;
          })
          .catch(ui.apiError);

        const updateGraderStatus = () => {
          api.Grader.status()
            .then((stats) => {
              commonNavbar.graderInfo = stats.grader;
              if (stats.grader.queue) {
                commonNavbar.graderQueueLength =
                  stats.grader.queue.run_queue_length +
                  stats.grader.queue.running.length;
              }
              commonNavbar.errorMessage = null;
            })
            .catch((stats) => {
              commonNavbar.errorMessage = stats.error;
            });
        };

        updateGraderStatus();
        setInterval(updateGraderStatus, 30000);
      }
    }

    // Setup preferred language
    this.preferredLanguage = options.preferredLanguage || null;

    // UI elements
    this.elements = {
      clarification: document.querySelector('#clarification'),
      clock: document.querySelector('#title .clock'),
      loadingOverlay: document.querySelector('#loading'),
      ranking: document.querySelector('#ranking div'),
      socketStatus: document.querySelector('#title .socket-status'),
    };

    if (document.getElementById('arena-navbar-problems') !== null) {
      this.navbarProblems = new Vue({
        el: '#arena-navbar-problems',
        components: { 'omegaup-arena-navbar-problems': arena_Navbar_Problems },
        data: () => ({
          problems: [],
          activeProblem: '',
        }),
        render: function (createElement) {
          return createElement('omegaup-arena-navbar-problems', {
            props: {
              problems: this.problems,
              activeProblem: this.activeProblem,
              inAssignment: !!options.courseAlias,
              digitsAfterDecimalPoint: options.partialScore ? 2 : 0,
              courseAlias: options.courseAlias,
              courseName: options.courseName,
              currentAssignment: self.currentProblemset,
            },
            on: {
              'navigate-to-problem': (request: ActiveProblem) => {
                window.location.hash = `#problems/${request.problem.alias}`;
              },
            },
          });
        },
      });
    }

    const navbar = document.getElementById('arena-navbar-payload');
    let navbarPayload = false;
    if (navbar !== null) {
      navbarPayload = JSON.parse(navbar.innerText);
    }

    if (
      document.getElementById('arena-navbar-miniranking') !== null &&
      this.options.contestAlias !== null
    ) {
      this.navbarMiniRanking = new Vue({
        el: '#arena-navbar-miniranking',
        components: {
          'omegaup-arena-navbar-miniranking': arena_Navbar_Miniranking,
        },
        data: () => ({
          showRanking: navbarPayload,
          users: [],
        }),
        render: function (createElement) {
          return createElement('omegaup-arena-navbar-miniranking', {
            props: {
              showRanking: this.showRanking,
              users: this.users,
            },
          });
        },
      });
    }

    if (this.elements.ranking) {
      this.scoreboard = new Vue({
        el: this.elements.ranking,
        components: {
          'omegaup-arena-scoreboard': arena_Scoreboard,
        },
        data: () => ({
          problems: [],
          ranking: [],
          lastUpdated: new Date(0),
          showPenalty: true,
        }),
        render: function (createElement) {
          return createElement('omegaup-arena-scoreboard', {
            props: {
              scoreboardColors: scoreboardColors,
              problems: this.problems,
              ranking: this.ranking,
              lastUpdated: this.lastUpdated,
              digitsAfterDecimalPoint: digitsAfterDecimalPoint,
              showPenalty: this.showPenalty,
              showInvitedUsersFilter: options.contestAlias !== null,
            },
          });
        },
      });
    }

    // Setup run details view, if available.
    if (document.getElementById('run-details') != null) {
      this.runDetailsView = new Vue({
        el: '#run-details',
        components: {
          'omegaup-arena-rundetails': arena_RunDetails,
        },
        data: () => ({
          data: null,
        }),
        render: function (createElement) {
          return createElement('omegaup-arena-rundetails', {
            props: {
              data: this.data,
            },
          });
        },
      });
    }

    // Setup run submit view, if it is available.
    if (document.getElementById('run-submit') !== null) {
      self.runSubmitView = new Vue({
        el: '#run-submit',
        components: {
          'omegaup-arena-runsubmit': arena_RunSubmit,
        },
        data: () => ({
          languages: [],
          preferredLanguage: '',
          nextSubmissionTimestamp: new Date(0),
        }),
        render: function (createElement) {
          return createElement('omegaup-arena-runsubmit', {
            props: {
              languages: this.languages,
              nextSubmissionTimestamp: this.nextSubmissionTimestamp,
              preferredLanguage: this.preferredLanguage,
            },
            on: {
              'submit-run': (code: string, language: string) => {
                self.submitRun(code, language);
              },
            },
            ref: 'component',
          });
        },
      });
    }

    // Setup any global hooks.
    this.bindGlobalHandlers();

    // Contest summary view model
    this.summaryView = new Vue({
      components: {
        'omegaup-arena-contestsummary': arena_ContestSummary,
      },
      data: () => ({
        contest: {
          start_time: new Date(),
          finish_time: null,
          window_length: 0,
          rerun_id: 0,
          title: '',
          director: '',
        },
      }),
      render: function (createElement) {
        return createElement('omegaup-arena-contestsummary', {
          props: {
            contest: this.contest,
          },
        });
      },
    });
    const summaryElement = document.getElementById('summary');
    if (summaryElement) {
      this.summaryView.$mount(summaryElement);
    }

    // Markdown view.
    this.markdownView = new Vue({
      components: {
        'omegaup-markdown': omegaup_Markdown,
      },
      data: () => ({
        markdown: '',
        imageMapping: {} as markdown.ImageMapping,
        sourceMapping: {} as markdown.SourceMapping,
        problemSettings: undefined as types.ProblemSettingsDistrib | undefined,
      }),
      render: function (createElement) {
        return createElement('omegaup-markdown', {
          props: {
            markdown: this.markdown,
            imageMapping: this.imageMapping,
            sourceMapping: this.sourceMapping,
            problemSettings: this.problemSettings,
          },
          on: {
            rendered: () => {
              self.onProblemRendered();
            },
          },
        });
      },
    });
    const problemStatementElement = document.querySelector(
      '#problem div.statement',
    );
    if (problemStatementElement) {
      this.markdownView.$mount(problemStatementElement);
    }

    this.navbarAssignments = null;
  }

  installProblemArtifactHooks(): void {
    $('.libinteractive-download form').on('submit', (e: Event) => {
      e.preventDefault();

      const form = e.target as HTMLElement;
      const alias = this.currentProblem.alias;
      const commit = this.currentProblem.commit;
      const os = (form.querySelector('.download-os') as HTMLInputElement)
        ?.value;
      const lang = (form.querySelector('.download-lang') as HTMLInputElement)
        ?.value;
      const extension = os == 'unix' ? '.tar.bz2' : '.zip';

      ui.navigateTo(
        `${window.location.protocol}//${window.location.host}/templates/${alias}/${commit}/${alias}_${os}_${lang}${extension}`,
      );
    });

    $('.libinteractive-download .download-lang').on('change', (e: Event) => {
      let form = e.target as HTMLElement;
      while (!form.classList.contains('libinteractive-download')) {
        if (!form.parentElement) {
          return;
        }
        form = form.parentElement;
      }
      $(form)
        .find('.libinteractive-extension')
        .html($(e.target as HTMLElement).val() as string);
    });

    $('.output-only-download a').attr(
      'href',
      `/probleminput/${this.currentProblem.alias}/${this.currentProblem.commit}/${this.currentProblem.alias}-input.zip`,
    );
  }

  connectSocket() {
    if (this.options.disableSockets || this.options.contestAlias == 'admin') {
      this.updateSocketStatus('✗', '#800');
      return;
    }

    const uri =
      `${window.location.protocol === 'https:' ? 'wss:' : 'ws:'}//${
        window.location.host
      }/events/?filter=/problemset/${this.options.problemsetId}` +
      (this.options.scoreboardToken ? `/${this.options.scoreboardToken}` : '');

    this.updateSocketStatus('↻', '#888');
    ui.reportEvent('events-socket', 'attempt');
    this.socket = new EventsSocket(uri, this);
    this.socket
      .connect()
      .then(() => {
        ui.reportEvent('events-socket', 'connected');
      })
      .catch((e) => {
        ui.reportEvent('events-socket', 'fallback');
        console.log(e);
        this.socket = null;
        setTimeout(() => {
          this.setupPolls();
        }, Math.random() * 15000);
      });
  }

  updateSocketStatus(status: string, color: string): void {
    if (!this.elements.socketStatus) {
      return;
    }
    this.elements.socketStatus.textContent = status;
    this.elements.socketStatus.style.color = color;
  }

  setupPolls(): void {
    this.refreshRanking();
    if (!this.options.contestAlias) {
      return;
    }
    this.refreshClarifications();

    if (!this.socket) {
      this.clarificationInterval = setInterval(() => {
        this.clarificationsOffset = 0; // Return pagination to start on refresh
        this.refreshClarifications();
      }, 5 * 60 * 1000);

      this.rankingInterval = setInterval(
        () => this.refreshRanking(),
        5 * 60 * 1000,
      );
    }
  }

  initProblemsetId(problemset: types.Problemset): void {
    if (!problemset.problemset_id) {
      return;
    }
    this.options.problemsetId = problemset.problemset_id;
  }

  initClock(start: Date, finish: Date | null, deadline: Date | null): void {
    this.startTime = start;
    this.finishTime = finish;
    // Once the clock is ready, we can now connect to the socket.
    this.connectSocket();
    if (!this.finishTime) {
      setItemText(this.elements.clock, null, '∞');
      return;
    }
    if (deadline) this.submissionDeadline = deadline;
    if (!this.clockInterval) {
      this.updateClock();
      this.clockInterval = setInterval(this.updateClock.bind(this), 1000);
    }
  }

  problemsetLoadedError(e: { start_time?: string }): void {
    console.error(e);
    if (!OmegaUp.loggedIn) {
      window.location.href = `/login/?redirect=${encodeURIComponent(
        window.location.pathname,
      )}`;
      return;
    }
    if (e.start_time) {
      const problemsetId = this.options.problemsetId;
      const problemsetStartTime = time.remoteDate(new Date(e.start_time));

      const problemsetCallback = () => {
        const now = Date.now();
        if (this.elements.loadingOverlay) {
          this.elements.loadingOverlay.textContent = `${problemsetId} ${time.formatDelta(
            problemsetStartTime.getTime() - now,
          )}`;
        }
        if (now < problemsetStartTime.getTime()) {
          setTimeout(problemsetCallback, 1000);
        } else {
          api.Problemset.details({ problemset_id: problemsetId })
            .then((result) => this.problemsetLoaded(result))
            .catch((e) => this.problemsetLoadedError(e));
        }
      };
      setTimeout(problemsetCallback, 1000);
      return;
    }
    if (this.elements.loadingOverlay) {
      this.elements.loadingOverlay.textContent = '404';
    }
  }

  problemsetLoaded(problemset: types.Problemset): void {
    if (typeof problemset.problemset_id !== 'undefined') {
      this.options.problemsetId = problemset.problemset_id;
    }

    if (typeof problemset.original_contest_alias !== 'undefined') {
      this.options.originalContestAlias = problemset.original_contest_alias;
    }

    if (typeof problemset.original_problemset_id !== 'undefined') {
      this.options.originalProblemsetId = problemset.original_problemset_id;
    }

    $('#title .contest-title').text(problemset.title ?? problemset.name ?? '');
    this.updateSummary({
      ...problemset,
      alias: problemset.alias as string,
      title: problemset.title as string,
      start_time: problemset.start_time as Date,
      admission_mode: problemset.admission_mode as
        | omegaup.AdmissionMode
        | undefined,
      requests_user_information: problemset.requests_user_information,
    });
    this.submissionGap = Math.max(0, problemset.submissions_gap ?? 60);

    this.initClock(
      problemset.start_time as Date,
      problemset.finish_time ?? null,
      problemset.submission_deadline ?? null,
    );
    this.initProblems(problemset);

    const problemSelect = this.elements.clarification?.querySelector('select');
    if (problemSelect) {
      for (const problem of problemset.problems ?? []) {
        const problemName = `${problem.letter}. ${ui.escape(problem.title)}`;

        if (this.navbarProblems) {
          this.navbarProblems.problems.push({
            alias: problem.alias,
            text: problemName,
            acceptsSubmissions: problem.languages !== '',
            bestScore: 0,
            maxScore: problem.points,
            hasRuns: false,
          });
        }

        $('<option>')
          .val(problem.alias)
          .text(problemName)
          .appendTo(problemSelect as Element);
      }
    }

    if (!this.options.isInterview) {
      this.setupPolls();
    }

    // Trigger the event (useful on page load).
    this.onHashChanged();

    if (this.elements.loadingOverlay) {
      this.elements.loadingOverlay.style.display = 'none';
    }
    $('#root').fadeIn('slow');

    if (
      typeof problemset.courseAssignments !== 'undefined' &&
      document.getElementById('arena-navbar-assignments') !== null &&
      this.navbarAssignments === null
    ) {
      const courseAlias = this.options.courseAlias;
      this.navbarAssignments = new Vue({
        el: '#arena-navbar-assignments',
        components: {
          'omegaup-arena-navbar-assignments': arena_Navbar_Assignments,
        },
        render: function (createElement) {
          return createElement('omegaup-arena-navbar-assignments', {
            props: {
              assignments: problemset.courseAssignments,
              currentAssignment: problemset,
            },
            on: {
              'navigate-to-assignment': (assignmentAlias: string) => {
                window.location.pathname = `/course/${courseAlias}/assignment/${assignmentAlias}/${
                  problemset.admin ? 'admin/' : ''
                }`;
              },
            },
          });
        },
      });
    }
  }

  initProblems(problemset: types.Problemset): void {
    this.currentProblemset = problemset;
    this.problemsetAdmin = problemset.admin ?? false;
    this.myRunsList.isProblemsetOpened =
      !Object.prototype.hasOwnProperty.call(problemset, 'opened') ||
      (problemset.opened ?? false);
    const problemsetProblems = problemset.problems ?? [];
    for (const problemsetProblem of problemsetProblems) {
      const alias = problemsetProblem.alias;
      this.problems[alias] = {
        ...problemsetProblem,
        languages: problemsetProblem.languages
          .split(',')
          .filter((language) => language !== ''),
      };
    }
    if (this.scoreboard) {
      this.scoreboard.problems = problemsetProblems;
      this.scoreboard.showPenalty = problemset.show_penalty ?? false;
    }
  }

  updateClock(): void {
    const countdownTime = this.submissionDeadline || this.finishTime;
    if (this.startTime === null || countdownTime === null || !OmegaUp.ready) {
      return;
    }

    const now = Date.now();
    let clock = '';
    if (now < this.startTime.getTime()) {
      clock = `-${time.formatDelta(this.startTime.getTime() - now)}`;
    } else if (now > countdownTime.getTime()) {
      // Contest for this user is over
      clock = '00:00:00';
      if (this.clockInterval) {
        clearInterval(this.clockInterval);
        this.clockInterval = null;
      }

      // Show go-to-practice-mode messages on contest end
      if (this.finishTime && now > this.finishTime.getTime()) {
        if (this.options.contestAlias) {
          ui.warning(
            `<a href="/arena/${this.options.contestAlias}/practice/">${T.arenaContestEndedUsePractice}</a>`,
          );
          this.myRunsList.isContestFinished = true;
        }
      }
    } else {
      clock = time.formatDelta(countdownTime.getTime() - now);
    }
    setItemText(this.elements.clock, null, clock);
  }

  updateRunFallback(guid: string): void {
    if (this.socket != null) return;
    setTimeout(() => {
      api.Run.status({ run_alias: guid })
        .then(time.remoteTimeAdapter)
        .then((response) => this.updateRun(response))
        .catch(ui.ignoreError);
    }, 5000);
  }

  updateRun(run: types.Run): void {
    this.trackRun(run);

    if (this.socket != null) return;

    if (run.status != 'ready') {
      this.updateRunFallback(run.guid);
      return;
    }
    if (this.options.contestAlias != 'admin') {
      this.refreshRanking();
    }
  }

  refreshRanking(): void {
    const scoreboardParams = {
      problemset_id:
        this.options.problemsetId || this.currentProblemset?.problemset_id,
      token: this.options.scoreboardToken,
    };

    if (this.options.contestAlias != null) {
      api.Problemset.scoreboard(scoreboardParams)
        .then((response) => {
          // Differentiate ranking change between virtual and normal contest
          if (this.options.originalContestAlias != null)
            this.virtualRankingChange(response);
          else this.rankingChange(response);
        })
        .catch(ui.ignoreError);
    } else if (
      this.options.problemsetAdmin ||
      this.options.contestAlias != null ||
      this.problemsetAdmin ||
      (this.options.courseAlias && this.options.assignmentAlias)
    ) {
      api.Problemset.scoreboard(scoreboardParams)
        .then((response) => this.rankingChange(response))
        .catch(ui.ignoreError);
    }
  }

  onVirtualRankingChange(virtualContestData: types.Scoreboard): void {
    // This clones virtualContestData to data so that virtualContestData values
    // won't be overriden by processes below
    const data = JSON.parse(
      JSON.stringify(virtualContestData.ranking),
    ) as types.Scoreboard;
    const dataRanking: (types.ScoreboardRankingEntry & {
      virtual?: boolean;
    })[] = data.ranking;
    const events = this.originalContestScoreboardEvents ?? [];
    const currentDelta =
      (new Date().getTime() - (this.startTime?.getTime() ?? 0)) / (1000 * 60);

    for (const rank of dataRanking) rank.virtual = true;

    const problemOrder: { [problemAlias: string]: number } = {};
    const problems: { order: number; alias: string }[] = [];
    const initialProblems: types.ScoreboardRankingProblem[] = [];

    for (const problem of Object.values(this.problems)) {
      problemOrder[problem.alias] = problems.length;
      initialProblems.push({
        alias: problem.alias,
        penalty: 0,
        percent: 0,
        points: 0,
        runs: 0,
      });
      problems.push({ order: problems.length + 1, alias: problem.alias });
    }

    // Calculate original contest scoreboard with current delta time
    const originalContestRanking: {
      [username: string]: types.ScoreboardRankingEntry;
    } = {};
    const originalContestEvents: types.ScoreboardEvent[] = [];

    // Refresh after time T
    let refreshTime = 30 * 1000; // 30 seconds

    events.forEach((evt) => {
      const key = evt.username;
      if (!Object.prototype.hasOwnProperty.call(originalContestRanking, key)) {
        originalContestRanking[key] = {
          country: evt.country,
          name: evt.name,
          username: evt.username,
          classname: evt.classname,
          is_invited: evt.is_invited,
          problems: Array.from(initialProblems),
          total: {
            penalty: 0,
            points: 0,
          },
          place: 0,
        };
      }
      if (evt.delta > currentDelta) {
        refreshTime = Math.min(
          refreshTime,
          (evt.delta - currentDelta) * 60 * 1000,
        );
        return;
      }
      originalContestEvents.push(evt);
      const problem =
        originalContestRanking[key].problems[problemOrder[evt.problem.alias]];
      originalContestRanking[key].problems[problemOrder[evt.problem.alias]] = {
        alias: evt.problem.alias,
        penalty: evt.problem.penalty,
        points: evt.problem.points,
        percent: evt.problem.points,
        // If problem appeared in event for than one, it means a problem has
        // been solved multiple times
        runs: problem ? problem.runs + 1 : 1,
      };
      originalContestRanking[key].total = evt.total;
    });
    // Merge original contest scoreboard ranking with virtual contest
    for (const ranking of Object.values(originalContestRanking)) {
      dataRanking.push(ranking);
    }

    // Re-sort rank
    dataRanking.sort((rank1, rank2) => {
      return rank2.total.points - rank1.total.points;
    });

    // Override ranking
    dataRanking.forEach((rank, index) => (rank.place = index + 1));
    this.onRankingChanged(data);

    api.Problemset.scoreboardEvents({
      problemset_id: this.options.problemsetId,
      token: this.options.scoreboardToken,
    })
      .then((response) => {
        // Change username to username-virtual
        for (const evt of response.events) {
          evt.username = ui.formatString(T.virtualSuffix, {
            username: evt.username,
          });
          evt.name = ui.formatString(T.virtualSuffix, { username: evt.name });
        }

        // Merge original contest and virtual contest scoreboard events
        this.onRankingEvents(response.events.concat(originalContestEvents));
      })
      .catch(ui.ignoreError);

    this.virtualContestRefreshInterval = setTimeout(() => {
      this.onVirtualRankingChange(virtualContestData);
    }, refreshTime);
  }

  /**
   * Merge original contest scoreboard and virtual contest
   */
  virtualRankingChange(scoreboard: types.Scoreboard): void {
    // Stop existing scoreboard simulation
    if (this.virtualContestRefreshInterval != null)
      clearTimeout(this.virtualContestRefreshInterval);

    if (this.originalContestScoreboardEvents == null) {
      api.Problemset.scoreboardEvents({
        problemset_id: this.options.originalProblemsetId,
      })
        .then((response) => {
          this.originalContestScoreboardEvents = response.events;
          this.onVirtualRankingChange(scoreboard);
        })
        .catch(ui.apiError);
    } else {
      this.onVirtualRankingChange(scoreboard);
    }
  }

  rankingChange(
    scoreboard: types.Scoreboard,
    rankingEvent: boolean = true,
  ): void {
    this.onRankingChanged(scoreboard);

    if (rankingEvent) {
      api.Problemset.scoreboardEvents({
        problemset_id:
          this.options.problemsetId || this.currentProblemset?.problemset_id,
        token: this.options.scoreboardToken,
      })
        .then((response) => this.onRankingEvents(response.events))
        .catch(ui.ignoreError);
    }
  }

  onRankingChanged(scoreboard: types.Scoreboard): void {
    if (this.navbarMiniRanking) {
      this.navbarMiniRanking.users = [];
    }

    if (this.removeRecentEventClassTimeout) {
      clearTimeout(this.removeRecentEventClassTimeout);
      this.removeRecentEventClassTimeout = null;
    }

    const ranking: types.ScoreboardRankingEntry[] = scoreboard.ranking || [];
    const newRanking: { [username: string]: number } = {};
    const order: { [problemAlias: string]: number } = {};
    const currentRankingState: { [username: string]: { place: number } } = {};

    for (let i = 0; i < scoreboard.problems.length; i++) {
      order[scoreboard.problems[i].alias] = i;
    }

    // Push scoreboard to ranking table
    for (let i = 0; i < ranking.length; i++) {
      const rank = ranking[i];
      newRanking[rank.username] = i;

      const username = ui.rankingUsername(rank);
      currentRankingState[username] = { place: rank.place ?? 0 };

      // Update problem scores.
      for (const alias of Object.keys(order)) {
        const problem = rank.problems[order[alias]];
        if (
          this.problems[alias] &&
          rank.username == OmegaUp.username &&
          this.problems[alias].languages.length > 0
        ) {
          const currentPoints = this.problems[alias].points;
          if (this.navbarProblems) {
            const currentProblem = this.navbarProblems.problems.find(
              (problem) => problem.alias === alias,
            );
            if (currentProblem) {
              currentProblem.hasRuns = problem.runs > 0;
              currentProblem.bestScore = problem.points;
              currentProblem.maxScore = currentPoints;
            }
          }
          this.updateProblemScore(alias, currentPoints, problem.points);
        }
      }

      // update miniranking
      if (i < 10) {
        if (this.navbarMiniRanking) {
          const username = ui.rankingUsername(rank);
          this.navbarMiniRanking.users.push({
            position: rank.place,
            username: username,
            country: rank.country,
            classname: rank.classname,
            points: rank.total.points,
            penalty: rank.total.penalty,
          });
        }
      }
    }

    if (this.scoreboard) {
      this.scoreboard.ranking = ranking;
      if (scoreboard.time) {
        this.scoreboard.lastUpdated = scoreboard.time;
      }
    }

    this.currentRanking = newRanking;
    this.prevRankingState = currentRankingState;
    this.removeRecentEventClassTimeout = setTimeout(() => {
      $('.recent-event').removeClass('recent-event');
    }, 30000);
  }

  onRankingEvents(events: types.ScoreboardEvent[]): void {
    const startTime = this.startTime?.getTime() ?? 0;

    const dataInSeries: { [name: string]: number[][] } = {};
    const navigatorData: number[][] = [[startTime, 0]];
    const series: (Highcharts.SeriesLineOptions & { rank: number })[] = [];
    const usernames: { [name: string]: string } = {};

    // Don't trust input data (data might not be sorted)
    events.sort((a, b) => a.delta - b.delta);

    this.currentEvents = events;

    // group points by person
    for (const curr of events) {
      // limit chart to top n users
      if (this.currentRanking[curr.username] > scoreboardColors.length - 1)
        continue;

      const name = curr.name ?? curr.username;

      if (!dataInSeries[name]) {
        dataInSeries[name] = [[startTime, 0]];
        usernames[name] = curr.username;
      }
      dataInSeries[name].push([
        startTime + curr.delta * 60 * 1000,
        curr.total.points,
      ]);

      // check if to add to navigator
      if (curr.total.points > navigatorData[navigatorData.length - 1][1]) {
        navigatorData.push([
          startTime + curr.delta * 60 * 1000,
          curr.total.points,
        ]);
      }
    }

    // convert datas to series
    for (const name of Object.keys(dataInSeries)) {
      dataInSeries[name].push([
        this.finishTime
          ? Math.min(this.finishTime.getTime(), Date.now())
          : Date.now(),
        dataInSeries[name][dataInSeries[name].length - 1][1],
      ]);
      series.push({
        type: 'line',
        name: name,
        rank: this.currentRanking[usernames[name]],
        data: dataInSeries[name],
        step: 'right',
      });
    }

    series.sort((a, b) => a.rank - b.rank);

    navigatorData.push([
      this.finishTime
        ? Math.min(this.finishTime.getTime(), Date.now())
        : Date.now(),
      navigatorData[navigatorData.length - 1][1],
    ]);
    this.createChart(series, navigatorData);
  }

  createChart(
    series: Highcharts.SeriesLineOptions[],
    navigatorSeries: number[][],
  ): void {
    if (series.length == 0 || !this.elements.ranking) return;

    this.rankingChart = Highcharts.stockChart(
      document.getElementById('ranking-chart') as HTMLElement,
      {
        chart: { height: 300, spacingTop: 20 },

        colors: scoreboardColors,

        xAxis: {
          ordinal: false,
          min: this.startTime?.getTime() ?? Date.now(),
          max: Math.min(this.finishTime?.getTime() || Infinity, Date.now()),
        },

        yAxis: {
          showLastLabel: true,
          showFirstLabel: false,
          min: 0,
          max: ((problems) => {
            let total = 0;
            for (const problem of Object.values(problems)) {
              total += problem.points;
            }
            return total;
          })(this.problems),
        },

        plotOptions: {
          series: {
            animation: false,
            lineWidth: 3,
            states: { hover: { lineWidth: 3 } },
            marker: { radius: 5, symbol: 'circle', lineWidth: 1 },
          },
        },

        navigator: {
          series: {
            type: 'line',
            step: true,
            lineWidth: 3,
            lineColor: '#333',
            data: navigatorSeries,
          },
        },

        rangeSelector: { enabled: false },

        series: series,
      } as Highcharts.Options,
    );
  }

  refreshClarifications(): void {
    api.Contest.clarifications({
      contest_alias: this.options.contestAlias,
      offset: this.clarificationsOffset,
      rowcount: this.clarificationsRowcount,
    })
      .then(time.remoteTimeAdapter)
      .then((response) => this.clarificationsChange(response.clarifications))
      .catch(ui.ignoreError);
  }

  updateClarification(clarification: types.Clarification): void {
    let r: HTMLElement | null = null;
    const anchor = `clarifications/clarification-${clarification.clarification_id}`;
    const clarifications =
      this.commonNavbar?.initialClarifications ?? this.initialClarifications;
    if (this.clarifications[clarification.clarification_id]) {
      r = this.clarifications[clarification.clarification_id];
      if (this.problemsetAdmin) {
        this.initialClarifications = clarifications.filter(
          (notification) =>
            notification.clarification_id !== clarification.clarification_id,
        );
        if (this.commonNavbar !== null) {
          this.commonNavbar.initialClarifications = this.initialClarifications;
        }
      } else {
        clarifications.push(clarification);
      }
    } else {
      r = document
        .querySelector('.clarifications tbody.clarification-list tr.template')
        ?.cloneNode(true) as HTMLElement | null;
      if (r) {
        r.classList.remove('template');
        r.classList.add('inserted');
        $('.clarifications tbody.clarification-list').prepend(r);
        this.clarifications[clarification.clarification_id] = r;
      }
      if (this.problemsetAdmin) {
        if (clarifications !== null) {
          clarifications.push(clarification);
        }
        if (!r) {
          return;
        }
        ((id, answerNode) => {
          const responseFormNode = $('.create-response-form', answerNode)
            .first()
            .removeClass('template');
          const cannedResponse = $(
            '.create-response-canned',
            answerNode,
          ).first();
          cannedResponse.on('change', () => {
            if (cannedResponse.val() === 'other') {
              $('.create-response-text', answerNode).first().show();
            } else {
              $('.create-response-text', answerNode).first().hide();
            }
          });
          if (clarification.public) {
            $('.create-response-is-public', responseFormNode)
              .first()
              .attr('checked', 'checked');
            $('.create-response-is-public', responseFormNode)
              .first()
              .prop('checked', true);
          }
          responseFormNode.on('submit', () => {
            let responseText: string = '';
            if (
              $('.create-response-canned', answerNode).first().val() === 'other'
            ) {
              responseText = String(
                $('.create-response-text', responseFormNode).first().val(),
              );
            } else {
              responseText = String(
                $(
                  '.create-response-canned>option:selected',
                  responseFormNode,
                ).html(),
              );
            }
            api.Clarification.update({
              clarification_id: id,
              answer: responseText,
              public: ($(
                '.create-response-is-public',
                responseFormNode,
              )[0] as HTMLInputElement).checked,
            })
              .then(() => {
                $('pre', answerNode).html(responseText);
                $('.create-response-text', answerNode).first().val('');
              })
              .catch(() => {
                $('pre', answerNode).html(responseText);
                $('.create-response-text', answerNode).first().val('');
              });
            return false;
          });
        })(clarification.clarification_id, $('.answer', r));
      }
    }

    if (r) {
      r.querySelector('.anchor')?.setAttribute('name', anchor);
      setItemText(r, '.contest', clarification.contest_alias ?? '');
      setItemText(r, '.problem', clarification.problem_alias);
      if (this.problemsetAdmin) {
        setItemText(r, '.author', clarification.author ?? '');
      }
      setItemText(r, '.time', time.formatTimestamp(clarification.time));
      setItemText(r, '.message', clarification.message);
      setItemText(r, '.answer pre', clarification.answer ?? '');
      if (clarification.answer) {
        this.answeredClarifications++;
      }

      if (clarification.answer == null) {
        $('.answer pre', r).hide();
        if (clarification.receiver != null) {
          $(r).addClass('direct-message');
        }
      } else {
        $('.answer pre', r).show();
        $(r).addClass('resolved');
      }
      if (clarification.public) {
        $('.create-response-is-public', r).first().prop('checked', true);
      }
    }
  }
  clarificationsChange(clarifications: types.Clarification[]): void {
    $('.clarifications tr.inserted').remove();
    if (
      clarifications.length > 0 &&
      clarifications.length < this.clarificationsRowcount
    ) {
      $('#clarifications-count').html(`(${clarifications.length})`);
    } else if (clarifications.length >= this.clarificationsRowcount) {
      $('#clarifications-count').html(`(${this.clarificationsRowcount}+)`);
    }

    const previouslyAnswered = this.answeredClarifications;
    this.answeredClarifications = 0;
    this.clarifications = {};

    for (let i = clarifications.length - 1; i >= 0; i--) {
      this.updateClarification(clarifications[i]);
    }

    if (this.commonNavbar !== null) {
      this.commonNavbar.initialClarifications = clarifications
        .filter((clarification) =>
          // Removing all unsolved clarifications.
          this.problemsetAdmin
            ? clarification.answer === null
            : clarification.answer !== null &&
              // Removing all unanswered clarifications.
              localStorage.getItem(
                `clarification-${clarification.clarification_id}`,
              ) === null,
        )
        .reverse();
    } else {
      this.initialClarifications = clarifications
        .filter((clarification) =>
          // Removing all unsolved clarifications.
          this.problemsetAdmin
            ? clarification.answer === null
            : clarification.answer !== null &&
              // Removing all unanswered clarifications.
              localStorage.getItem(
                `clarification-${clarification.clarification_id}`,
              ) === null,
        )
        .reverse();
    }
    if (
      this.answeredClarifications > previouslyAnswered &&
      this.activeTab != 'clarifications'
    ) {
      $('#clarifications-count').css('font-weight', 'bold');
    }
  }

  updateAllowedLanguages(languages: string[]): void {
    const canSubmit = languages.length !== 0;

    $('.runs').toggle(canSubmit);
    $('.data').toggle(canSubmit);
    $('.best-solvers').toggle(canSubmit);

    if (this.runSubmitView) {
      this.runSubmitView.languages = languages;
      this.runSubmitView.preferredLanguage = this.preferredLanguage;
    }
  }

  onHashChanged(): void {
    let tabChanged = false;
    let foundHash = false;
    const tabs = ['summary', 'problems', 'ranking', 'clarifications', 'runs'];

    for (const tabName of tabs) {
      if (window.location.hash.indexOf(`#${tabName}`) == 0) {
        tabChanged = this.activeTab != tabName;
        this.activeTab = tabName;
        foundHash = true;

        break;
      }
    }

    if (!foundHash) {
      // Change the URL to the deafult tab but don't break the back button.
      window.history.replaceState({}, '', `#${this.activeTab}`);
    }

    const problemMatch = /#problems\/([^/]+)(\/new-run)?/.exec(
      window.location.hash,
    );
    // Check if we were already viewing this problem to avoid reloading
    // it and repainting the screen.
    let problemChanged = true;
    if (
      this.previousHash == `${window.location.hash}/new-run` ||
      window.location.hash == `${this.previousHash}/new-run`
    ) {
      problemChanged = false;
    }
    this.previousHash = window.location.hash;

    if (problemMatch && this.problems[problemMatch[1]]) {
      const newRun = problemMatch[2];
      const problem = (this.currentProblem = this.problems[problemMatch[1]]);

      // Set link to edit problem
      document
        .querySelector('#edit-problem-link')
        ?.setAttribute('href', `/problem/${problem.alias}/edit/`);

      // Set as active the selected problem
      if (this.navbarProblems) {
        this.navbarProblems.activeProblem = this.currentProblem.alias;
      }

      const update = (problem: Problem) => {
        // TODO: Make #problem a component
        $('#summary').hide();
        $('#problem').show();
        if (this.problemSettingsSummary !== null) {
          this.problemSettingsSummary.problem = problem;
        } else if (document.getElementById('problem-settings-summary')) {
          this.problemSettingsSummary = new Vue({
            el: '#problem-settings-summary',
            components: {
              'omegaup-problem-settings-summary': problem_SettingsSummary,
            },
            data: () => ({
              problem: problem,
            }),
            render: function (createElement) {
              return createElement('omegaup-problem-settings-summary', {
                props: { problem: this.problem },
              });
            },
          });
        }
        this.renderProblem(problem);
        const karelLangs = ['kp', 'kj'];
        if (karelLangs.every((x) => problem.languages.indexOf(x) != -1)) {
          let originalHref = $('#problem .karel-js-link a').attr('href');
          if (originalHref) {
            const hashIndex = originalHref.indexOf('#');
            if (hashIndex != -1) {
              originalHref = originalHref.substring(0, hashIndex);
            }
          }
          if (problem.settings?.cases.sample) {
            $('#problem .karel-js-link a').attr(
              'href',
              `${originalHref}#mundo:${encodeURIComponent(
                problem.settings.cases.sample.in,
              )}`,
            );
          } else {
            $('#problem .karel-js-link a').attr('href', originalHref ?? null);
          }
          $('#problem .karel-js-link').removeClass('hide');
        } else {
          $('#problem .karel-js-link').addClass('hide');
        }
        if (problem.source) {
          $('#problem .source span').html(ui.escape(problem.source));
          $('#problem .source').show();
        } else {
          $('#problem .source').hide();
        }
        if (problem.problemsetter) {
          $('#problem .problemsetter a')
            .html(ui.escape(problem.problemsetter.name))
            .attr('href', `/profile/${problem.problemsetter.username}/`);
          $('#problem .problemsetter').show();
        } else {
          $('#problem .problemsetter').hide();
        }

        $('#problem tbody.added').remove();

        const updateRuns = (runs?: types.Run[]) => {
          if (runs) {
            for (const run of runs) {
              this.trackRun(run);
            }
          }
          this.myRunsList.problemAlias = problem.alias;
        };

        updateRuns(problem.runs);
        this.showQualityNominationPopup();

        if (!this.options.courseAlias) {
          this.initSubmissionCountdown();
        }
      };

      if (problemChanged) {
        // Ping Analytics with updated problem id
        const page = window.location.pathname + window.location.hash;
        if (typeof window.ga == 'function') {
          window.ga('set', 'page', page);
          window.ga('send', 'pageview');
        }
        if (problem.statement) {
          update(problem);
        } else {
          const problemset = this.computeProblemsetArg();
          api.Problem.details(
            $.extend(problemset, {
              problem_alias: problem.alias,
              prevent_problemset_open:
                this.problemsetAdmin && !this.myRunsList.isProblemsetOpened,
            }),
          )
            .then((problem_ext) => {
              problem.source = problem_ext.source;
              problem.problemsetter = problem_ext.problemsetter;
              if (problem_ext.statement) {
                problem.statement = problem_ext.statement;
              }
              problem.settings = problem_ext.settings;
              problem.input_limit = problem_ext.input_limit ?? 10240;
              problem.runs = problem_ext.runs;
              this.preferredLanguage = problem_ext.preferred_language ?? null;
              update(problem);
            })
            .catch(ui.apiError);
        }
      }

      if (newRun) {
        $('#overlay form:not([data-run-submit])').hide();
        $('#overlay').show();
        if (this.options.shouldShowFirstAssociatedIdentityRunWarning) {
          this.options.shouldShowFirstAssociatedIdentityRunWarning = false;
          ui.warning(T.firstSumbissionWithIdentity);
        }
      }
    } else if (this.activeTab == 'problems') {
      $('#problem').hide();
      $('#summary').show();
      if (this.navbarProblems) {
        this.navbarProblems.activeProblem = null;
      }
    } else if (this.activeTab == 'clarifications') {
      if (window.location.hash == '#clarifications/new') {
        $('#overlay form').hide();
        $('#overlay, #clarification').show();
      }
    }
    this.detectShowRun();

    if (tabChanged) {
      $('.tabs a.active').removeClass('active');
      $(`.tabs a[href="#${this.activeTab}"]`).addClass('active');
      $('.tab').hide();
      $(`#${this.activeTab}`).show();

      if (this.activeTab == 'ranking') {
        if (this.currentEvents) {
          this.onRankingEvents(this.currentEvents);
        }
      } else if (this.activeTab == 'clarifications') {
        $('#clarifications-count').css('font-weight', 'normal');
      }
    }
  }

  showQualityNominationPopup(): void {
    const qualityPayload = this.currentProblem.quality_payload;
    if (!qualityPayload) {
      // Quality Nomination only works for Courses
      return;
    }
    if (!qualityPayload.canNominateProblem) {
      // Only real users can perform this action.
      return;
    }
    if (this.qualityNominationForm !== null) {
      this.qualityNominationForm.qualityPayload = qualityPayload;
      return;
    }
    this.qualityNominationForm = new Vue({
      el: '#qualitynomination-popup',
      components: {
        'qualitynomination-popup': qualitynomination_Popup,
      },
      data: () => ({
        qualityPayload: qualityPayload,
      }),
      mounted: () => {
        ui.reportEvent('quality-nomination', 'shown');
      },
      render: function (createElement) {
        return createElement('qualitynomination-popup', {
          props: {
            nominated: this.qualityPayload.nominated,
            nominatedBeforeAc: this.qualityPayload.nominatedBeforeAc,
            solved: this.qualityPayload.solved,
            tried: this.qualityPayload.tried,
            dismissed: this.qualityPayload.dismissed,
            dismissedBeforeAc: this.qualityPayload.dismissedBeforeAc,
            canNominateProblem: this.qualityPayload.canNominateProblem,
            problemAlias: this.qualityPayload.problemAlias,
          },
          on: {
            submit: (popup: qualitynomination_Popup) => {
              const contents = {
                before_ac: !popup.solved && popup.tried,
                difficulty:
                  popup.difficulty !== ''
                    ? Number.parseInt(popup.difficulty, 10)
                    : 0,
                tags: popup.tags.length > 0 ? popup.tags : [],
                quality:
                  popup.quality !== '' ? Number.parseInt(popup.quality, 10) : 0,
              };
              api.QualityNomination.create({
                problem_alias: popup.problemAlias,
                nomination: 'suggestion',
                contents: JSON.stringify(contents),
              })
                .then(() => {
                  ui.reportEvent('quality-nomination', 'submit');
                })
                .catch(ui.apiError);
            },
            dismiss: (popup: qualitynomination_Popup) => {
              const contents = {
                before_ac: !popup.solved && popup.tried,
              };
              api.QualityNomination.create({
                problem_alias: popup.problemAlias,
                nomination: 'dismissal',
                contents: JSON.stringify(contents),
              })
                .then(() => {
                  ui.info(T.qualityNominationRateProblemDesc);
                  ui.reportEvent('quality-nomination', 'dismiss');
                })
                .catch(ui.apiError);
            },
          },
        });
      },
    });
  }

  renderProblem(problem: Problem): void {
    this.currentProblem = problem;
    if (problem.statement) {
      this.markdownView.markdown = problem.statement.markdown;
      this.markdownView.imageMapping = problem.statement.images;
      this.markdownView.sourceMapping = problem.statement.sources;
      this.markdownView.problemSettings = problem.settings;
    }
    const creationDateElement = document.querySelector(
      '#problem .problem-creation-date',
    ) as HTMLElement;
    if (problem.problemsetter?.creation_date && creationDateElement) {
      creationDateElement.innerText = ui.formatString(T.wordsUploadedOn, {
        date: time.formatDate(problem.problemsetter.creation_date),
      });
    }
  }

  onProblemRendered(): void {
    const libinteractiveInterfaceNameElement = this.markdownView.$el.querySelector(
      'span.libinteractive-interface-name',
    ) as HTMLElement;
    if (
      libinteractiveInterfaceNameElement &&
      this.currentProblem.settings?.interactive?.module_name
    ) {
      libinteractiveInterfaceNameElement.innerText = this.currentProblem.settings.interactive.module_name.replace(
        /\.idl$/,
        '',
      );
    }
    this.installProblemArtifactHooks();

    this.updateAllowedLanguages(this.currentProblem.languages);

    this.ephemeralGrader.send('setSettings', this.currentProblem.settings);
  }

  detectShowRun(): void {
    const showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    const showRunMatch = window.location.hash.match(showRunRegex);
    if (!showRunMatch) {
      return;
    }
    $('#overlay form:not([data-run-submit])').hide();
    $('#overlay').show();
    const guid = showRunMatch[1];
    api.Run.details({ run_alias: guid })
      .then(time.remoteTimeAdapter)
      .then((data) => {
        if (
          data.show_diff === 'none' ||
          (this.options.contestAlias && this.options.contestAlias !== 'admin')
        ) {
          this.displayRunDetails(guid, data);
          return;
        }
        fetch(`/api/run/download/run_alias/${guid}/show_diff/true/`)
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
              if (extension !== 'out' || relativePath.indexOf('/') !== -1) {
                return;
              }
              if (
                data.show_diff === 'examples' &&
                relativePath.indexOf('sample/') === 0
              ) {
                return;
              }
              result.cases.push(basename);
              result.promises.push(zip.file(zipEntry.name).async('text'));
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
            this.displayRunDetails(guid, data);
          })
          .catch(ui.apiError);
      })
      .catch((error) => {
        ui.apiError(error);
        this.hideOverlay();
      });
  }

  hideOverlay(): void {
    if ($('#overlay').css('display') === 'none') return;
    $('#overlay').hide();
    window.location.hash = window.location.hash.substring(
      0,
      window.location.hash.lastIndexOf('/'),
    );
  }

  bindGlobalHandlers(): void {
    $('#overlay, .close').on('click', (e: Event) => this.onCloseSubmit(e));
  }

  onCloseSubmit(e: Event): void {
    if (
      (e.target as HTMLElement).id !== 'overlay' &&
      (e.target as HTMLElement).closest('button.close') === null
    ) {
      return;
    }
    this.hideOverlay();
    e.preventDefault();
  }

  initSubmissionCountdown(): void {
    let nextSubmissionTimestamp = new Date(0);
    const problem = this.problems[this.currentProblem.alias];
    if (typeof problem !== 'undefined') {
      if (typeof problem.nextSubmissionTimestamp !== 'undefined') {
        nextSubmissionTimestamp = new Date(problem.nextSubmissionTimestamp);
      } else if (
        typeof problem.runs !== 'undefined' &&
        typeof this.currentProblemset?.submissions_gap !== 'undefined' &&
        problem.runs.length > 0
      ) {
        nextSubmissionTimestamp = new Date(
          problem.runs[problem.runs.length - 1].time.getTime() +
            this.currentProblemset.submissions_gap * 1000,
        );
      }
    }
    if (this.runSubmitView) {
      this.runSubmitView.nextSubmissionTimestamp = nextSubmissionTimestamp;
    }
  }

  computeProblemsetArg(): { contest_alias?: string; problemset_id?: number } {
    if (this.options.contestAlias) {
      return { contest_alias: this.options.contestAlias };
    }
    if (this.options.problemsetId) {
      return { problemset_id: this.options.problemsetId };
    }
    return {};
  }

  submitRun(code: string, language: string): void {
    const problemset = this.computeProblemsetArg();

    api.Run.create(
      Object.assign(problemset, {
        problem_alias: this.currentProblem.alias,
        language: language,
        source: code,
      }),
    )
      .then((response) => {
        ui.reportEvent('submission', 'submit');
        if (this.options.isLockdownMode && sessionStorage) {
          sessionStorage.setItem(`run:${response.guid}`, code);
        }

        const currentProblem = this.problems[this.currentProblem.alias];
        currentProblem.lastSubmission = new Date();
        currentProblem.nextSubmissionTimestamp =
          response.nextSubmissionTimestamp;
        const run = {
          guid: response.guid,
          submit_delay: response.submit_delay,
          username: this.options.payload.currentUsername,
          classname: this.options.payload.userClassname,
          country: 'xx',
          status: 'new',
          alias: this.currentProblem.alias,
          time: new Date(),
          penalty: 0,
          runtime: 0,
          memory: 0,
          verdict: 'JE',
          score: 0,
          language: language,
        };
        this.updateRun(run);
        if (this.runSubmitView) {
          const component = this.runSubmitView.$refs
            .component as arena_RunSubmit;
          component.clearForm();
          // Wait until the code view has been cleared before hiding the
          // overlay. Not doing so will sometimes cause the contents of the
          // editor to still be visible when the overlay is shown again.
          component.$nextTick(() => this.hideOverlay());
        } else {
          this.hideOverlay();
        }
        if (!this.options.courseAlias) {
          this.initSubmissionCountdown();
        }
      })
      .catch((run) => {
        alert(run.error ?? run);
        if (run.errorname) {
          ui.reportEvent('submission', 'submit-fail', run.errorname);
        }
      });
  }

  updateSummary(contest: omegaup.Contest): void {
    this.summaryView.contest = contest;
  }

  displayRunDetails(guid: string, data: messages.RunDetailsResponse): void {
    let sourceHTML,
      sourceLink = false;
    if (data.source?.indexOf('data:') === 0) {
      sourceLink = true;
      sourceHTML = data.source;
    } else if (data.source == 'lockdownDetailsDisabled') {
      sourceHTML =
        (typeof sessionStorage !== 'undefined' &&
          sessionStorage.getItem(`run:${guid}`)) ||
        T.lockdownDetailsDisabled;
    } else {
      sourceHTML = data.source;
    }

    const numericSort = <T extends { [key: string]: any }>(key: string) => {
      const isDigit = (ch: string) => '0' <= ch && ch <= '9';
      return (x: T, y: T) => {
        let i = 0,
          j = 0;
        for (; i < x[key].length && j < y[key].length; i++, j++) {
          if (isDigit(x[key][i]) && isDigit(x[key][j])) {
            let nx = 0,
              ny = 0;
            while (i < x[key].length && isDigit(x[key][i]))
              nx = nx * 10 + parseInt(x[key][i++]);
            while (j < y[key].length && isDigit(y[key][j]))
              ny = ny * 10 + parseInt(y[key][j++]);
            i--;
            j--;
            if (nx != ny) return nx - ny;
          } else if (x[key][i] < y[key][j]) {
            return -1;
          } else if (x[key][i] > y[key][j]) {
            return 1;
          }
        }
        return x[key].length - i - (y[key].length - j);
      };
    };
    const detailsGroups = data.details && data.details.groups;
    let groups = undefined;
    if (detailsGroups && detailsGroups.length) {
      detailsGroups.sort(numericSort('group'));
      for (const detailGroup of detailsGroups) {
        if (!detailGroup.cases) {
          continue;
        }
        detailGroup.cases.sort(numericSort('name'));
      }
      groups = detailsGroups;
    }
    if (this.runDetailsView) {
      this.runDetailsView.data = Object.assign({}, data, {
        logs: data.logs || '',
        judged_by: data.judged_by || '',
        source: sourceHTML,
        source_link: sourceLink,
        source_url: window.URL.createObjectURL(
          new Blob([data.source || ''], { type: 'text/plain' }),
        ),
        source_name: `Main.${data.language}`,
        groups: groups,
        show_diff:
          !this.options.contestAlias || this.options.contestAlias === 'admin'
            ? data.show_diff
            : 'none',
        feedback: ((this.options.contestAlias &&
          this.currentProblemset?.feedback) ||
          'detailed') as omegaup.SubmissionFeedback,
      });
      const runDetailsView: HTMLElement | null = document.querySelector(
        '[data-run-details-view]',
      );
      if (runDetailsView) runDetailsView.style.display = 'block';
    }
  }

  trackRun(run: types.Run): void {
    runsStore.commit('addRun', run);
    if (run.username !== OmegaUp.username) {
      return;
    }
    myRunsStore.commit('addRun', run);
    if (run.status !== 'ready') {
      return;
    }
    const problem = this.problems[run.alias];
    if (!problem) {
      return;
    }
    this.updateProblemScore(run.alias, problem.points, 0);
    const qualityPayload = problem.quality_payload;
    if (!qualityPayload) {
      return;
    }
    if (run.verdict !== 'AC' && run.verdict !== 'CE' && run.verdict !== 'JE') {
      qualityPayload.tried = true;
    }
    if (run.verdict === 'AC') {
      qualityPayload.solved = true;
    }
    if (problem.alias === this.currentProblem.alias) {
      this.showQualityNominationPopup();
    }
  }

  updateProblemScore(
    alias: string,
    maxScore: number,
    previousScore: number,
  ): void {
    // It only works for contests
    if (this.options.contestAlias != null && this.scoreboard !== null) {
      this.scoreboard.ranking = this.scoreboard.ranking.map((rank) => {
        const ranking = rank;
        if (ranking.username == OmegaUp.username) {
          ranking.problems = rank.problems.map((problem) => {
            const problemRanking = problem;
            if (problemRanking.alias == alias) {
              const maxScore = getMaxScore(
                myRunsStore.state.runs,
                problemRanking.alias,
                previousScore,
              );
              problemRanking.points = maxScore;
            }
            return problemRanking;
          });
          ranking.total.points = rank.problems.reduce(
            (accumulator, problem) => accumulator + problem.points,
            0,
          );
        }
        return ranking;
      });
    }
    if (this.navbarProblems) {
      const currentProblem = this.navbarProblems.problems.find(
        (problem) => problem.alias === alias,
      );
      if (currentProblem) {
        currentProblem.bestScore = getMaxScore(
          myRunsStore.state.runs,
          alias,
          previousScore,
        );
        currentProblem.maxScore = maxScore;
      }
    }
  }
}

export function GetDefaultOptions(): ArenaOptions {
  return {
    isLockdownMode: false,
    isInterview: false,
    disableClarifications: false,
    disableSockets: false,
    assignmentAlias: null,
    contestAlias: null,
    courseAlias: null,
    courseName: null,
    scoreboardToken: null,
    shouldShowFirstAssociatedIdentityRunWarning: false,
    originalContestAlias: null,
    partialScore: true,
    problemsetId: null,
    problemsetAdmin: false,
    payload: {
      associatedIdentities: [],
      omegaUpLockDown: false,
      bootstrap4: false,
      inContest: false,
      isLoggedIn: false,
      isReviewer: false,
      gravatarURL51: '',
      gravatarURL128: '',
      currentEmail: '',
      currentName: undefined,
      currentUsername: '',
      userClassname: 'user-rank-unranked',
      userCountry: 'xx',
      profileProgress: 0,
      isMainUserIdentity: false,
      isAdmin: false,
      lockDownImage: '',
      navbarSection: '',
    },
    preferredLanguage: null,
  };
}

export function GetOptionsFromLocation(
  arenaLocation: Location | URL,
): ArenaOptions {
  const options = GetDefaultOptions();

  if (
    document.getElementsByTagName('body')[0].className.indexOf('lockdown') !==
    -1
  ) {
    options.isLockdownMode = true;
    window.onbeforeunload = (e: BeforeUnloadEvent) => {
      e.preventDefault();
      e.returnValue = T.lockdownMessageWarning;
    };
  }

  if (arenaLocation.pathname.indexOf('/arena/problem/') === -1) {
    const match = /\/arena\/([^/]+)\/?/.exec(arenaLocation.pathname);
    if (match) {
      options.contestAlias = match[1];
    }
  }

  if (arenaLocation.search.indexOf('ws=off') !== -1) {
    options.disableSockets = true;
  }
  if (document.getElementById('payload')) {
    const payload = types.payloadParsers.CommonPayload() as types.CommonPayload & {
      shouldShowFirstAssociatedIdentityRunWarning?: boolean;
      contest?: omegaup.Contest;
      preferred_language?: string;
    };
    if (payload !== null) {
      options.shouldShowFirstAssociatedIdentityRunWarning =
        payload.shouldShowFirstAssociatedIdentityRunWarning || false;
      options.preferredLanguage = payload.preferred_language || null;
      options.partialScore = payload.contest?.partial_score ?? true;
      options.payload = payload;
    }
  }
  return options;
}

export class EventsSocket {
  uri: string;
  arena: Arena;
  shouldRetry: boolean = false;
  socket: WebSocket | null = null;
  socketKeepalive: ReturnType<typeof setTimeout> | null = null;
  retries: number = 10;

  constructor(uri: string, arena: Arena) {
    this.uri = uri;
    this.arena = arena;
  }

  connect(): Promise<void> {
    this.shouldRetry = false;
    return new Promise((accept, reject) => {
      try {
        const socket = new WebSocket(this.uri, 'com.omegaup.events');

        socket.onmessage = (message) => this.onmessage(message);
        socket.onopen = () => {
          this.shouldRetry = true;
          this.arena.updateSocketStatus('•', '#080');
          this.socketKeepalive = setInterval(
            () => socket.send('"ping"'),
            30000,
          );
          accept();
        };
        socket.onclose = (e: Event) => {
          this.onclose(e);
          reject(e);
        };

        this.socket = socket;
      } catch (e) {
        reject(e);
        return;
      }
    });
  }

  onmessage(message: MessageEvent) {
    const data = JSON.parse(message.data);

    if (data.message == '/run/update/') {
      data.run.time = time.remoteTime(data.run.time * 1000);
      this.arena.updateRun(data.run);
    } else if (data.message == '/clarification/update/') {
      if (!this.arena.options.disableClarifications) {
        data.clarification.time = time.remoteTime(
          data.clarification.time * 1000,
        );
        this.arena.updateClarification(data.clarification);
      }
    } else if (data.message == '/scoreboard/update/') {
      data.time = time.remoteTime(data.time * 1000);
      if (this.arena.problemsetAdmin && data.scoreboard_type != 'admin') {
        if (this.arena.options.originalContestAlias == null) return;
        this.arena.virtualRankingChange(data.scoreboard);
        return;
      }
      this.arena.rankingChange(data.scoreboard);
    }
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  onclose(e: Event) {
    this.socket = null;
    if (this.socketKeepalive) {
      clearInterval(this.socketKeepalive);
      this.socketKeepalive = null;
    }
    if (this.shouldRetry && this.retries > 0) {
      this.retries--;
      this.arena.updateSocketStatus('↻', '#888');
      setTimeout(() => this.connect(), Math.random() * 15000);
      return;
    }

    this.arena.updateSocketStatus('✗', '#800');
  }
}

export class EphemeralGrader {
  ephemeralEmbeddedGraderElement: null | HTMLIFrameElement;
  messageQueue: { method: string; params: any[] }[];
  loaded: boolean;

  constructor() {
    this.ephemeralEmbeddedGraderElement = document.getElementById(
      'ephemeral-embedded-grader',
    ) as null | HTMLIFrameElement;
    this.messageQueue = [];
    this.loaded = false;

    if (!this.ephemeralEmbeddedGraderElement) return;

    this.ephemeralEmbeddedGraderElement.onload = () => {
      this.loaded = true;
      this.messageQueue.slice(0).forEach((message) => {
        this._sendInternal(message);
      });
    };
  }

  send(method: string, ...params: any[]): void {
    const message = {
      method: method,
      params: params,
    };

    if (!this.loaded) {
      this.messageQueue.push(message);
      return;
    }
    this._sendInternal(message);
  }

  _sendInternal(message: { method: string; params: any[] }): void {
    if (!this.ephemeralEmbeddedGraderElement) {
      return;
    }
    (this.ephemeralEmbeddedGraderElement.contentWindow as Window).postMessage(
      message,
      `${window.location.origin}/grader/ephemeral/embedded/`,
    );
  }
}
