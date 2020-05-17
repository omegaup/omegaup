// @ts-nocheck

import Vue from 'vue';

import * as api from '../api';
import T from '../lang';
import * as markdown from '../markdown';
import { omegaup, OmegaUp } from '../omegaup';
import { types, messages } from '../api_types';
import * as time from '../time';
import * as typeahead from '../typeahead';
import * as ui from '../ui';

import arena_CodeView from '../components/arena/CodeView.vue';
import arena_ContestSummary from '../components/arena/ContestSummary.vue';
import arena_Navbar_Assignments from '../components/arena/NavbarAssignments.vue';
import arena_Navbar_Miniranking from '../components/arena/NavbarMiniranking.vue';
import arena_Navbar_Problems from '../components/arena/NavbarProblems.vue';
import arena_RunDetails from '../components/arena/RunDetails.vue';
import arena_Runs from '../components/arena/Runs.vue';
import arena_Scoreboard from '../components/arena/Scoreboard.vue';
import common_Navbar from '../components/common/Navbar.vue';
import notification_Clarifications from '../components/notification/Clarifications.vue';
import qualitynomination_Popup from '../components/qualitynomination/Popup.vue';

import ArenaAdmin from './admin_arena';
import {
  ArenaOptions,
  EphemeralGrader,
  EventsSocket,
  GetOptionsFromLocation,
  getMaxScore,
  scoreboardColors,
  myRunsStore,
  runsStore,
} from './arena_transitional';

import * as Markdown from '@/third_party/js/pagedown/Markdown.Converter.js';
import * as Highcharts from 'highcharts/highstock';

export { ArenaAdmin, GetOptionsFromLocation };

// Number of digits after the decimal point to show.
const digitsAfterDecimalPoint: number = 2;

export class Arena {
  options: ArenaOptions;

  // Currently opened problem.
  currentProblem: types.ProblemsetProblem = {
    accepted: 0,
    alias: '',
    commit: '',
    difficulty: 0,
    languages: '',
    letter: '',
    order: 0,
    points: 0,
    submissions: 0,
    title: '',
    version: '',
    visibility: 0,
    visits: 0,
  };

  // The current problemset.
  currentProblemset: types.Problemset | null = null;

  // The interval for clock updates.
  clockInterval = null;

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
    [alias: string]: types.ContestProblem | types.ProblemsetProblem;
  } = {};

  // WebSocket for real-time updates.
  socket: EventsSocket | null = null;

  // The offset of each user into the ranking table.
  currentRanking: { [username: string]: number } = {};

  // The previous ranking information. Useful to show diffs.
  prevRankingState: { [username: string]: { place: number } } = {};

  // Every time a recent event is shown, have this interval clear it after
  // 30s.
  removeRecentEventClassTimeout = null;

  // The last known scoreboard event stream.
  currentEvents: types.ScoreboardEvent[] = [];

  // The Markdown-to-HTML converter.
  markdownConverter: Markdown.Converter = markdown.markdownConverter();

  // If we have admin powers in this contest.
  problemsetAdmin: boolean = false;

  preferredLanguage: string | null;

  answeredClarifications: number = 0;
  clarificationsOffset: number = 0;
  clarificationsRowcount: number = 20;
  activeTab: string = 'problems';
  clarifications = {};
  submissionGap: number = 0;

  // The interval of time that submissions button will be disabled
  submissionGapInterval: number = 0;

  // Cache scoreboard data for virtual contest
  originalContestScoreboardEvents: types.ScoreboardEvent[] | null = null;

  // Virtual contest refresh interval
  virtualContestRefreshInterval = null;

  // Ephemeral grader support.
  ephemeralGrader: EphemeralGrader = new EphemeralGrader();

  // UI elements
  elements: {
    clarification: JQuery;
    clock: JQuery;
    loadingOverlay: JQuery;
    ranking: JQuery;
    socketStatus: JQuery;
    submitForm: JQuery & { code: JQuery; file: JQuery; language: JQuery };
  };

  codeEditor:
    | (Vue & {
        language: string;
        code: string;
        refresh: () => void;
      })
    | null = null;

  navbarAssignments: Vue | null = null;

  navbarProblems:
    | (Vue & {
        problems: omegaup.ContestProblem[];
        activeProblem: string;
      })
    | null = null;

  navbarMiniRanking:
    | (Vue & {
        showRanking: boolean;
        users: omegaup.UserRank[];
      })
    | null = null;

  myRunsList: Vue;

  qualityNominationForm = null;

  commonNavbar:
    | (Vue & {
        initialClarifications: types.Clarification[];
        graderInfo: types.GraderStatus | null;
        errorMessage: string | null;
        graderQueueLength: number;
        notifications: types.Notification[];
      })
    | null = null;

  runDetailsView:
    | (Vue & {
        data: omegaup.RunDetails;
      })
    | null = null;

  scoreboard:
    | (Vue & {
        problems: omegaup.Problem[];
        ranking: types.ScoreboardRankingEntry[];
        lastUpdated: Date;
      })
    | null = null;

  summaryView: Vue & { contest: omegaup.Contest };

  rankingChart: Highcharts.Chart | null = null;

  constructor(options: ArenaOptions) {
    this.options = options;

    // All runs in this contest/problem.
    this.myRunsList = new Vue({
      render: function(createElement) {
        return createElement('omegaup-arena-runs', {
          props: {
            contestAlias: options.contestAlias,
            isContestFinished:
              this.isContestFinished &&
              !options.isPractice &&
              !options.isOnlyProblem,
            isProblemsetOpened: this.isProblemsetOpened,
            problemAlias: this.problemAlias,
            runs: myRunsStore.state.runs,
            showDetails: true,
            showPoints: !options.isPractice && !options.isOnlyProblem,
          },
          on: {
            details: (run: types.Run) => {
              window.location.hash += `/show-run:${run.guid}`;
            },
          },
        });
      },
      data: {
        isContestFinished: false,
        isProblemsetOpened: true,
        problemAlias: options.isOnlyProblem ? options.onlyProblemAlias : null,
      },
      components: { 'omegaup-arena-runs': arena_Runs },
    });
    const myRunsListElement = document.querySelector('#problem table.runs');
    if (myRunsListElement) {
      this.myRunsList.$mount(myRunsListElement);
    }

    // Currently opened clarification notifications.
    if (document.getElementById('common-navbar')) {
      const commonNavbar = (this.commonNavbar = new Vue({
        el: '#common-navbar',
        render: function(createElement) {
          return createElement('omegaup-common-navbar', {
            props: {
              omegaUpLockDown: options.payload.omegaUpLockDown,
              inContest: options.payload.inContest,
              isLoggedIn: options.payload.isLoggedIn,
              isReviewer: options.payload.isReviewer,
              gravatarURL51: options.payload.gravatarURL51,
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
        data: {
          graderInfo: null,
          graderQueueLength: -1,
          errorMessage: null,
          initialClarifications: [],
          notifications: [],
        },
        components: {
          'omegaup-common-navbar': common_Navbar,
        },
      }));

      if (this.options.payload.isAdmin) {
        api.Notification.myList({})
          .then(data => {
            commonNavbar.notifications = data.notifications;
          })
          .catch(ui.apiError);

        const updateGraderStatus = () => {
          api.Grader.status()
            .then(stats => {
              commonNavbar.graderInfo = stats.grader;
              if (stats.grader.queue) {
                commonNavbar.graderQueueLength =
                  stats.grader.queue.run_queue_length +
                  stats.grader.queue.running.length;
              }
              commonNavbar.errorMessage = null;
            })
            .catch(stats => {
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
      clarification: $('#clarification'),
      clock: $('#title .clock'),
      loadingOverlay: $('#loading'),
      ranking: $('#ranking div'),
      socketStatus: $('#title .socket-status'),
      submitForm: $.extend($('#submit'), {
        code: $('#submit textarea[name="code"]'),
        file: $('#submit input[type="file"]'),
        language: $('#submit select[name="language"]'),
      }),
    };

    if (document.getElementById('arena-navbar-problems') !== null) {
      this.navbarProblems = new Vue({
        el: '#arena-navbar-problems',
        render: function(createElement) {
          return createElement('omegaup-arena-navbar-problems', {
            props: {
              problems: this.problems,
              activeProblem: this.activeProblem,
              inAssignment: !!options.courseAlias,
            },
            on: {
              'navigate-to-problem': (problemAlias: string) => {
                window.location.hash = `#problems/${problemAlias}`;
              },
            },
          });
        },
        data: {
          problems: [],
          activeProblem: '',
        },
        components: { 'omegaup-arena-navbar-problems': arena_Navbar_Problems },
      });
    }

    const navbar = document.getElementById('arena-navbar-payload');
    let navbarPayload = false;
    if (navbar !== null) {
      navbarPayload = JSON.parse(navbar.innerText);
    }

    if (document.getElementById('arena-navbar-miniranking') !== null) {
      this.navbarMiniRanking = new Vue({
        el: '#arena-navbar-miniranking',
        render: function(createElement) {
          return createElement('omegaup-arena-navbar-miniranking', {
            props: {
              showRanking: this.showRanking,
              users: this.users,
            },
          });
        },
        data: {
          showRanking: navbarPayload,
          users: [],
        },
        components: {
          'omegaup-arena-navbar-miniranking': arena_Navbar_Miniranking,
        },
      });
    }

    if (this.elements.ranking.length) {
      this.scoreboard = new Vue({
        el: this.elements.ranking[0],
        render: function(createElement) {
          return createElement('omegaup-scoreboard', {
            props: {
              scoreboardColors: scoreboardColors,
              problems: this.problems,
              ranking: this.ranking,
              lastUpdated: this.lastUpdated,
              digitsAfterDecimalPoint: digitsAfterDecimalPoint,
            },
          });
        },
        data: {
          problems: [],
          ranking: [],
          lastUpdated: new Date(0),
        },
        components: {
          'omegaup-scoreboard': arena_Scoreboard,
        },
      });
    }

    // Setup run details view, if available.
    if (document.getElementById('run-details') != null) {
      this.runDetailsView = new Vue({
        el: '#run-details',
        render: function(createElement) {
          return createElement('omegaup-arena-rundetails', {
            props: {
              data: this.data,
            },
          });
        },
        data: { data: null },
        components: {
          'omegaup-arena-rundetails': arena_RunDetails,
        },
      });
    }

    // Setup any global hooks.
    this.bindGlobalHandlers();

    // Contest summary view model
    this.summaryView = new Vue({
      render: function(createElement) {
        return createElement('omegaup-arena-contestsummary', {
          props: {
            contest: this.contest,
            showRanking: !options.isPractice,
          },
        });
      },
      data: {
        contest: {
          start_time: new Date(),
          finish_time: null,
          window_length: 0,
          rerun_id: 0,
          title: '',
          director: '',
        },
      },
      components: {
        'omegaup-arena-contestsummary': arena_ContestSummary,
      },
    });
    const summaryElement = document.getElementById('summary');
    if (summaryElement) {
      this.summaryView.$mount(summaryElement);
    }

    this.navbarAssignments = null;
  }

  installLibinteractiveHooks() {
    let self = this;
    $('.libinteractive-download form').on('submit', (e: Event) => {
      e.preventDefault();

      let form = $(e.target);
      let alias = self.currentProblem.alias;
      let commit = self.currentProblem.commit;
      let os = $('.download-os', form).val();
      let lang = $('.download-lang', form).val();
      let extension = os == 'unix' ? '.tar.bz2' : '.zip';

      ui.navigateTo(
        `${window.location.protocol}//${window.location.host}/templates/${alias}/${commit}/${alias}_${os}_${lang}${extension}`,
      );
    });

    $('.libinteractive-download .download-lang').on('change', (e: Event) => {
      let form = <HTMLElement>e.target;
      while (!form.classList.contains('libinteractive-download')) {
        form = form.parentElement;
        if (!form) {
          return;
        }
      }
      $(form)
        .find('.libinteractive-extension')
        .html($(e.target).val());
    });
  }

  connectSocket() {
    let self = this;
    if (
      self.options.isPractice ||
      self.options.disableSockets ||
      self.options.contestAlias == 'admin'
    ) {
      self.elements.socketStatus.html('✗').css('color', '#800');
      return false;
    }

    let protocol = window.location.protocol === 'https:' ? 'wss://' : 'ws://';
    let uris = [];
    // Backendv2 uri
    uris.push(
      protocol +
        window.location.host +
        '/events/?filter=/problemset/' +
        self.options.problemsetId +
        (self.options.scoreboardToken
          ? '/' + self.options.scoreboardToken
          : ''),
    );

    const connect = (uris: string[], index: number) => {
      self.socket = new EventsSocket(uris[index], self);
      self.socket.connect().catch(e => {
        console.log(e);
        // Try the next uri.
        index++;
        if (index < uris.length) {
          connect(uris, index);
        } else {
          // Out of options. Falling back to polls.
          self.socket = null;
          setTimeout(() => {
            self.setupPolls();
          }, Math.random() * 15000);
        }
      });
    };

    self.elements.socketStatus.html('↻').css('color', '#888');
    connect(uris, 0);
  }

  setupPolls() {
    let self = this;
    self.refreshRanking();
    if (!self.options.contestAlias) {
      return;
    }
    self.refreshClarifications();

    if (!self.socket) {
      self.clarificationInterval = setInterval(() => {
        self.clarificationsOffset = 0; // Return pagination to start on refresh
        self.refreshClarifications();
      }, 5 * 60 * 1000);

      self.rankingInterval = setInterval(
        self.refreshRanking.bind(self),
        5 * 60 * 1000,
      );
    }
  }

  initProblemsetId(problemset) {
    let self = this;
    if (!problemset.hasOwnProperty('problemset_id')) {
      return;
    }
    self.options.problemsetId = problemset.problemset_id;
  }

  initClock(start: Date, finish: Date, deadline: Date | null) {
    let self = this;

    self.startTime = start;
    self.finishTime = finish;
    // Once the clock is ready, we can now connect to the socket.
    self.connectSocket();
    if (self.options.isPractice || !self.finishTime) {
      self.elements.clock.html('&infin;');
      return;
    }
    if (deadline) self.submissionDeadline = deadline;
    if (!self.clockInterval) {
      self.updateClock();
      self.clockInterval = setInterval(self.updateClock.bind(self), 1000);
    }
  }

  problemsetLoaded(problemset: types.Problemset) {
    let self = this;
    if (problemset.status == 'error') {
      if (!OmegaUp.loggedIn) {
        window.location = '/login/?redirect=' + escape(window.location);
      } else if (problemset.start_time) {
        let f = ((x, y) => {
          return () => {
            let t = new Date();
            self.elements.loadingOverlay.html(
              `${x} ${time.formatDelta(y.getTime() - t.getTime())}`,
            );
            if (t.getTime() < y.getTime()) {
              setTimeout(f, 1000);
            } else {
              api.Problemset.details({ problemset_id: x })
                .then(problemsetLoaded.bind(self))
                .catch(ui.ignoreError);
            }
          };
        })(self.options.problemsetId, problemset.start_time);
        setTimeout(f, 1000);
      } else {
        self.elements.loadingOverlay.html('404');
      }
      return;
    }
    if (
      self.options.isPractice &&
      problemset.finish_time &&
      Date.now() < problemset.finish_time.getTime()
    ) {
      window.location = window.location.pathname.replace(/\/practice.*/, '/');
      return;
    }

    if (problemset.hasOwnProperty('problemset_id')) {
      self.options.problemsetId = problemset.problemset_id;
    }

    if (problemset.hasOwnProperty('original_contest_alias')) {
      self.options.originalContestAlias = problemset.original_contest_alias;
    }

    if (problemset.hasOwnProperty('original_problemset_id')) {
      self.options.originalProblemsetId = problemset.original_problemset_id;
    }

    $('#title .contest-title').html(
      ui.escape(problemset.title || problemset.name),
    );
    self.updateSummary(problemset);
    self.submissionGap = parseInt(problemset.submission_gap);

    if (!(self.submissionGap > 0)) self.submissionGap = 0;

    self.initClock(
      problemset.start_time,
      problemset.finish_time,
      problemset.submission_deadline,
    );
    self.initProblems(problemset);

    let problemSelect = $('select', self.elements.clarification);
    for (let idx in problemset.problems) {
      let problem = problemset.problems[idx];
      let problemName = `${problem.letter}. ${ui.escape(problem.title)}`;

      if (self.navbarProblems) {
        self.navbarProblems.problems.push({
          alias: problem.alias,
          text: problemName,
          acceptsSubmissions: problem.languages !== '',
          bestScore: 0,
          maxScore: problem.points,
        });
      }

      $('<option>')
        .val(problem.alias)
        .text(problemName)
        .appendTo(problemSelect);
    }

    if (!self.options.isPractice && !self.options.isInterview) {
      self.setupPolls();
    }

    // Trigger the event (useful on page load).
    self.onHashChanged();

    self.elements.loadingOverlay.fadeOut('slow');
    $('#root').fadeIn('slow');

    if (
      typeof problemset.courseAssignments !== 'undefined' &&
      document.getElementById('arena-navbar-assignments') !== null &&
      self.navbarAssignments === null
    ) {
      self.navbarAssignments = new Vue({
        el: '#arena-navbar-assignments',
        render: function(createElement) {
          return createElement('omegaup-arena-navbar-assignments', {
            props: {
              assignments: problemset.courseAssignments,
              currentAssignmentAlias: problemset.alias,
            },
            on: {
              'navigate-to-assignment': (assignmentAlias: string) => {
                window.location.pathname = `/course/${
                  self.options.courseAlias
                }/assignment/${assignmentAlias}/${
                  problemset.admin ? 'admin/' : ''
                }`;
              },
            },
          });
        },
        components: {
          'omegaup-arena-navbar-assignments': arena_Navbar_Assignments,
        },
      });
    }
  }

  initProblems(problemset: types.Problemset) {
    let self = this;
    self.currentProblemset = problemset;
    self.problemsetAdmin = problemset.admin;
    self.myRunsList.isProblemsetOpened =
      !problemset.hasOwnProperty('opened') || problemset.opened;
    let problems = problemset.problems;
    for (let i = 0; i < problems.length; i++) {
      let problem = problems[i];
      let alias = problem.alias;
      if (typeof problem.runs === 'undefined') {
        problem.runs = [];
      }
      self.problems[alias] = problem;
    }
    if (self.scoreboard) {
      self.scoreboard.problems = problems;
      self.scoreboard.showPenalty = problemset.show_penalty;
    }
  }

  updateClock() {
    let self = this;
    let countdownTime = self.submissionDeadline || self.finishTime;
    if (self.startTime === null || countdownTime === null || !OmegaUp.ready) {
      return;
    }

    let now = Date.now();
    let clock = '';
    if (now < self.startTime.getTime()) {
      clock = `-${time.formatDelta(self.startTime.getTime() - now)}`;
    } else if (now > countdownTime.getTime()) {
      // Contest for self user is over
      clock = '00:00:00';
      clearInterval(self.clockInterval);
      self.clockInterval = null;

      // Show go-to-practice-mode messages on contest end
      if (now > self.finishTime.getTime()) {
        if (self.options.contestAlias) {
          ui.warning(
            `<a href="/arena/${self.options.contestAlias}/practice/">${T.arenaContestEndedUsePractice}</a>`,
          );
          self.myRunsList.isContestFinished = true;
        }
      }
    } else {
      clock = time.formatDelta(countdownTime.getTime() - now);
    }
    self.elements.clock.text(clock);
  }

  updateRunFallback(guid: string): void {
    if (this.socket != null) return;
    setTimeout(() => {
      api.Run.status({ run_alias: guid })
        .then(time.remoteTimeAdapter)
        .then(response => this.updateRun(response))
        .catch(ui.ignoreError);
    }, 5000);
  }

  updateRun(run) {
    let self = this;

    self.trackRun(run);

    if (self.socket != null) return;

    if (run.status != 'ready') {
      self.updateRunFallback(run.guid);
      return;
    }
    if (
      !self.options.isPractice &&
      !self.options.isOnlyProblem &&
      self.options.contestAlias != 'admin'
    ) {
      self.refreshRanking();
    }
  }

  refreshRanking() {
    let self = this;
    let scoreboardParams = {
      problemset_id:
        self.options.problemsetId || self.currentProblemset.problemset_id,
    };
    if (self.options.scoreboardToken) {
      scoreboardParams.token = self.options.scoreboardToken;
    }

    if (self.options.contestAlias != null) {
      api.Problemset.scoreboard(scoreboardParams)
        .then(response => {
          // Differentiate ranking change between virtual and normal contest
          if (self.options.originalContestAlias != null)
            self.virtualRankingChange(response);
          else self.rankingChange(response);
        })
        .catch(ui.ignoreError);
    } else if (
      self.options.problemsetAdmin ||
      self.options.contestAlias != null ||
      self.problemsetAdmin ||
      (self.options.courseAlias && self.options.assignmentAlias)
    ) {
      api.Problemset.scoreboard(scoreboardParams)
        .then(response => self.rankingChange(response))
        .catch(ui.ignoreError);
    }
  }

  onVirtualRankingChange(virtualContestData: types.Scoreboard): void {
    // This clones virtualContestData to data so that virtualContestData values
    // won't be overriden by processes below
    const data = <types.Scoreboard>(
      JSON.parse(JSON.stringify(virtualContestData.ranking))
    );
    const dataRanking = <
      (types.ScoreboardRankingEntry & { virtual?: boolean })[]
    >data.ranking;
    const events = this.originalContestScoreboardEvents ?? [];
    const currentDelta =
      (new Date().getTime() - (this.startTime?.getTime() ?? 0)) / (1000 * 60);

    for (const rank of dataRanking) rank.virtual = true;

    let problemOrder: { [problemAlias: string]: number } = {};
    let problems: { order: number; alias: string }[] = [];
    let initialProblems: types.ScoreboardRankingProblem[] = [];

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

    events.forEach(evt => {
      const key = evt.username;
      if (!originalContestRanking.hasOwnProperty(key)) {
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
      .then(response => {
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
        .then(response => {
          this.originalContestScoreboardEvents = response.events;
          this.onVirtualRankingChange(scoreboard);
        })
        .catch(ui.apiError);
    } else {
      this.onVirtualRankingChange(scoreboard);
    }
  }

  rankingChange(scoreboard: types.Scoreboard, rankingEvent = true): void {
    this.onRankingChanged(scoreboard);

    if (rankingEvent) {
      api.Problemset.scoreboardEvents({
        problemset_id:
          this.options.problemsetId || this.currentProblemset?.problemset_id,
        token: this.options.scoreboardToken,
      })
        .then(response => this.onRankingEvents(response.events))
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
      let rank = ranking[i];
      newRanking[rank.username] = i;

      let username = ui.rankingUsername(rank);
      currentRankingState[username] = { place: rank.place };

      // Update problem scores.
      let totalRuns = 0;
      for (const alias of Object.keys(order)) {
        let problem = rank.problems[order[alias]];
        totalRuns += problem.runs;

        if (
          this.problems[alias] &&
          rank.username == OmegaUp.username &&
          this.problems[alias].languages !== ''
        ) {
          const currentPoints = parseFloat(this.problems[alias].points || '0');
          if (this.navbarProblems) {
            const currentProblem = this.navbarProblems.problems.find(
              problem => problem.alias === alias,
            );
            currentProblem.bestScore = problem.points;
            currentProblem.maxScore = currentPoints;
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
    if (series.length == 0 || this.elements.ranking.length == 0) return;

    this.rankingChart = Highcharts.stockChart(
      <HTMLElement>document.getElementById('ranking-chart'),
      <Highcharts.Options>{
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
          max: (problems => {
            let total = 0;
            for (let prob in problems) {
              if (!problems.hasOwnProperty(prob)) continue;
              total += problems[prob].points;
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
      },
    );
  }

  refreshClarifications(): void {
    api.Contest.clarifications({
      contest_alias: this.options.contestAlias,
      offset: this.clarificationsOffset,
      rowcount: this.clarificationsRowcount,
    })
      .then(time.remoteTimeAdapter)
      .then(response => this.clarificationsChange(response.clarifications))
      .catch(ui.ignoreError);
  }

  updateClarification(clarification) {
    let self = this;
    let r = null;
    let anchor = `clarifications/clarification-${clarification.clarification_id}`;
    if (self.commonNavbar === null) {
      return;
    }
    const clarifications = self.commonNavbar.initialClarifications;
    if (self.clarifications[clarification.clarification_id]) {
      r = self.clarifications[clarification.clarification_id];
      if (self.problemsetAdmin) {
        self.commonNavbar.initialClarifications = clarifications.filter(
          notification =>
            notification.clarification_id !== clarification.clarification_id,
        );
      } else {
        clarifications.push(clarification);
      }
    } else {
      r = $('.clarifications tbody.clarification-list tr.template')
        .clone()
        .removeClass('template')
        .addClass('inserted');

      if (self.problemsetAdmin) {
        if (clarifications !== null) {
          clarifications.push(clarification);
        }
        ((id, answerNode) => {
          let responseFormNode = $(
            '#create-response-form',
            answerNode,
          ).removeClass('template');
          let cannedResponse = $('#create-response-canned', answerNode);
          cannedResponse.on('change', () => {
            if (cannedResponse.val() === 'other') {
              $('#create-response-text', answerNode).show();
            } else {
              $('#create-response-text', answerNode).hide();
            }
          });
          if (clarification.public) {
            $('#create-response-is-public', responseFormNode).attr(
              'checked',
              'checked',
            );
            $('#create-response-is-public', responseFormNode).prop(
              'checked',
              true,
            );
          }
          responseFormNode.on('submit', () => {
            let responseText = null;
            if ($('#create-response-canned', answerNode).val() === 'other') {
              responseText = $('#create-response-text', this).val();
            } else {
              responseText = $(
                '#create-response-canned>option:selected',
                this,
              ).html();
            }
            api.Clarification.update({
              clarification_id: id,
              answer: responseText,
              public: $('#create-response-is-public', this)[0].checked ? 1 : 0,
            })
              .then(() => {
                $('pre', answerNode).html(responseText);
                $('#create-response-text', answerNode).val('');
              })
              .catch(() => {
                $('pre', answerNode).html(responseText);
                $('#create-response-text', answerNode).val('');
              });
            return false;
          });
        })(clarification.clarification_id, $('.answer', r));
      }
    }

    $('.anchor', r).attr('name', anchor);
    $('.contest', r).html(clarification.contest_alias);
    $('.problem', r).html(clarification.problem_alias);
    if (self.problemsetAdmin) $('.author', r).html(clarification.author);
    $('.time', r).html(
      Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', clarification.time.getTime()),
    );
    $('.message', r).html(ui.escape(clarification.message));
    $('.answer pre', r).html(ui.escape(clarification.answer));
    if (clarification.answer) {
      self.answeredClarifications++;
    }

    if (!self.clarifications[clarification.clarification_id]) {
      $('.clarifications tbody.clarification-list').prepend(r);
      self.clarifications[clarification.clarification_id] = r;
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
      $('#create-response-is-public', r).prop('checked', true);
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
      $('#clarifications-count').html(`(${clarifications.length}+)`);
    }

    let previouslyAnswered = this.answeredClarifications;
    this.answeredClarifications = 0;
    this.clarifications = {};

    for (let i = clarifications.length - 1; i >= 0; i--) {
      this.updateClarification(clarifications[i]);
    }

    if (this.commonNavbar !== null) {
      this.commonNavbar.initialClarifications = clarifications
        .filter(clarification =>
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

  updateAllowedLanguages(lang_array) {
    const allowedLanguages = [
      { language: '', name: '' },
      { language: 'kp', name: 'Karel (Pascal)' },
      { language: 'kj', name: 'Karel (Java)' },
      { language: 'c', name: 'C11 (gcc 7.4)' },
      { language: 'c11-gcc', name: 'C11 (gcc 7.4)' },
      { language: 'c11-clang', name: 'C11 (clang 6.0)' },
      { language: 'cpp', name: 'C++03 (g++ 7.4)' },
      { language: 'cpp11', name: 'C++11 (g++ 7.4)' },
      { language: 'cpp11-gcc', name: 'C++11 (g++ 7.4)' },
      { language: 'cpp11-clang', name: 'C++11 (clang++ 6.0)' },
      { language: 'cpp17-gcc', name: 'C++17 (g++ 7.4)' },
      { language: 'cpp17-clang', name: 'C++17 (clang++ 6.0)' },
      { language: 'java', name: 'Java (openjdk 11.0)' },
      { language: 'py', name: 'Python 2.7' },
      { language: 'py2', name: 'Python 2.7' },
      { language: 'py3', name: 'Python 3.6' },
      { language: 'rb', name: 'Ruby (2.5)' },
      { language: 'pl', name: 'Perl (5.26)' },
      { language: 'cs', name: 'C# (dotnet 2.2)' },
      { language: 'pas', name: 'Pascal (fpc 3.0)' },
      { language: 'cat', name: 'Output Only' },
      { language: 'hs', name: 'Haskell (ghc 8.0)' },
      { language: 'lua', name: 'Lua (5.2)' },
    ];

    let self = this;

    let can_submit = lang_array.length != 0;

    $('.runs').toggle(can_submit);
    $('.data').toggle(can_submit);
    $('.best-solvers').toggle(can_submit);

    // refresh options in select
    const languageSelect = document.querySelector('select[name="language"]');
    while (languageSelect.firstChild)
      languageSelect.removeChild(languageSelect.firstChild);

    const languageArray =
      typeof lang_array === 'string' ? lang_array.split(',') : lang_array;
    languageArray.push('');

    allowedLanguages
      .filter(item => {
        return languageArray.includes(item.language);
      })
      .forEach(optionItem => {
        let optionNode = document.createElement('option');
        optionNode.value = optionItem.language;
        optionNode.appendChild(document.createTextNode(optionItem.name));
        languageSelect.appendChild(optionNode);
      });
  }

  selectDefaultLanguage() {
    let self = this;
    let langElement = self.elements.submitForm.language;

    if (self.preferredLanguage) {
      $('option', langElement).each(() => {
        let option = $(this);
        if (option.val() != self.preferredLanguage) return;
        option.prop('selected', true);
        return false;
      });
    }
    if (langElement.val()) return;

    $('option', langElement).each(() => {
      let option = $(this);

      option.prop('selected', true);
      langElement.trigger('change');
      return false;
    });
  }

  mountEditor(problem: types.ProblemsetProblem): void {
    const lang = <string>(this.elements.submitForm.language.val() ?? '');
    let template = '';
    if (lang && problem.settings?.interactive?.templates?.[lang]) {
      template = problem.settings.interactive.templates[lang];
    }
    if (this.codeEditor) {
      this.codeEditor.code = template;
      return;
    }

    const codeEditor = (this.codeEditor = new Vue({
      el: this.elements.submitForm.code[0],
      render: function(createElement) {
        return createElement('omegaup-arena-code-view', {
          props: {
            language: this.language,
            value: this.code,
          },
          on: {
            input: (value: string) => {
              codeEditor.code = value;
            },
            change: (value: string) => {
              codeEditor.code = value;
            },
          },
        });
      },
      data: {
        language: lang,
        code: template,
      },
      methods: {
        refresh: () => {
          (<arena_CodeView>codeEditor.$children[0]).refresh();
        },
      },
      components: {
        'omegaup-arena-code-view': arena_CodeView,
      },
    }));
  }

  onHashChanged() {
    let self = this;
    let tabChanged = false;
    let foundHash = false;
    let tabs = ['summary', 'problems', 'ranking', 'clarifications', 'runs'];

    for (let i = 0; i < tabs.length; i++) {
      if (window.location.hash.indexOf('#' + tabs[i]) == 0) {
        tabChanged = self.activeTab != tabs[i];
        self.activeTab = tabs[i];
        foundHash = true;

        break;
      }
    }

    if (!foundHash) {
      // Change the URL to the deafult tab but don't break the back button.
      window.history.replaceState({}, '', '#' + self.activeTab);
    }

    let problem = /#problems\/([^\/]+)(\/new-run)?/.exec(window.location.hash);
    // Check if we were already viewing this problem to avoid reloading
    // it and repainting the screen.
    let problemChanged = true;
    if (
      self.previousHash == window.location.hash + '/new-run' ||
      window.location.hash == self.previousHash + '/new-run'
    ) {
      problemChanged = false;
    }
    self.previousHash = window.location.hash;

    if (problem && self.problems[problem[1]]) {
      let newRun = problem[2];
      self.currentProblem = problem = self.problems[problem[1]];
      // Set as active the selected problem
      if (self.navbarProblems) {
        self.navbarProblems.activeProblem = self.currentProblem.alias;
      }

      const update = problem => {
        // TODO: Make #problem a component
        $('#summary').hide();
        $('#problem').show();
        $('#problem > .title').text(
          `${problem.letter}. ${ui.escape(problem.title)}`,
        );
        $('#problem .data .points').text(problem.points);
        $('#problem .memory_limit').text(
          `${problem.settings.limits.MemoryLimit / 1024 / 1024} MiB`,
        );
        $('#problem .time_limit').text(problem.settings.limits.TimeLimit);
        $('#problem .overall_wall_time_limit').text(
          problem.settings.limits.OverallWallTimeLimit,
        );
        $('#problem .input_limit').text(`${problem.input_limit / 1024} KiB`);
        self.renderProblem(problem);
        let karel_langs = ['kp', 'kj'];
        if (karel_langs.every(x => problem.languages.indexOf(x) != -1)) {
          let original_href = $('#problem .karel-js-link a').attr('href');
          let hash_index = original_href.indexOf('#');
          if (hash_index != -1) {
            original_href = original_href.substring(0, hash_index);
          }
          if (problem.settings.cases.sample) {
            $('#problem .karel-js-link a').attr(
              'href',
              original_href +
                '#mundo:' +
                encodeURIComponent(problem.settings.cases.sample.in),
            );
          } else {
            $('#problem .karel-js-link a').attr('href', original_href);
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

        const updateRuns = runs => {
          if (runs) {
            for (let run of runs) {
              self.trackRun(run);
            }
          }
          self.myRunsList.problemAlias = problem.alias;
        };

        if (self.options.isPractice || self.options.isOnlyProblem) {
          api.Problem.runs({ problem_alias: problem.alias })
            .then(time.remoteTimeAdapter)
            .then(data => {
              updateRuns(data.runs);
            })
            .catch(ui.apiError);
        } else {
          updateRuns(problem.runs);
          self.showQualityNominationPopup();
        }

        self.initSubmissionCountdown();
      };

      if (problemChanged) {
        // Ping Analytics with updated problem id
        let page = window.location.pathname + window.location.hash;
        if (typeof ga == 'function') {
          ga('set', 'page', page);
          ga('send', 'pageview');
        }
        if (problem.statement) {
          update(problem);
        } else {
          let problemset = self.computeProblemsetArg();
          api.Problem.details(
            $.extend(problemset, {
              problem_alias: problem.alias,
              prevent_problemset_open:
                self.problemsetAdmin && !self.myRunsList.isProblemsetOpened,
            }),
          )
            .then(problem_ext => {
              problem.source = problem_ext.source;
              problem.problemsetter = problem_ext.problemsetter;
              problem.statement = problem_ext.statement;
              problem.settings = problem_ext.settings;
              problem.input_limit = problem_ext.input_limit;
              problem.runs = problem_ext.runs;
              problem.templates = problem_ext.templates;
              self.preferredLanguage = problem_ext.preferred_language;
              update(problem);
            })
            .catch(ui.apiError);
        }
      }

      if (newRun) {
        $('#overlay form').hide();
        $('input', self.elements.submitForm).show();
        self.elements.submitForm.show();
        $('#overlay').show();
        if (self.codeEditor) {
          // It might not be mounted yet if we refresh directly onto
          // a /new-run view. This code executes directly, whereas
          // codeEditor is mounted after update() finishes.
          //
          // Luckily in this case we don't require the call to refresh
          // for the display to update correctly!
          self.codeEditor.refresh();
        }
        if (self.options.shouldShowFirstAssociatedIdentityRunWarning) {
          self.options.shouldShowFirstAssociatedIdentityRunWarning = false;
          ui.warning(T.firstSumbissionWithIdentity);
        }
      }
    } else if (self.activeTab == 'problems') {
      $('#problem').hide();
      $('#summary').show();
      if (self.navbarProblems) {
        self.navbarProblems.activeProblem = null;
      }
    } else if (self.activeTab == 'clarifications') {
      if (window.location.hash == '#clarifications/new') {
        $('#overlay form').hide();
        $('#overlay, #clarification').show();
      }
    }
    self.detectShowRun();

    if (tabChanged) {
      $('.tabs a.active').removeClass('active');
      $('.tabs a[href="#' + self.activeTab + '"]').addClass('active');
      $('.tab').hide();
      $('#' + self.activeTab).show();

      if (self.activeTab == 'ranking') {
        if (self.currentEvents) {
          self.onRankingEvents(self.currentEvents);
        }
      } else if (self.activeTab == 'clarifications') {
        $('#clarifications-count').css('font-weight', 'normal');
      }
    }
  }

  showQualityNominationPopup() {
    let self = this;

    let qualityPayload = self.currentProblem.quality_payload;
    if (typeof qualityPayload === 'undefined') {
      // Quality Nomination only works for Courses
      return;
    }
    if (!qualityPayload.canNominateProblem) {
      // Only real users can perform this action.
      return;
    }
    if (self.qualityNominationForm !== null) {
      self.qualityNominationForm.nominated = qualityPayload.nominated;
      self.qualityNominationForm.nominatedBeforeAC =
        qualityPayload.nominatedBeforeAC;
      self.qualityNominationForm.solved = qualityPayload.solved;
      self.qualityNominationForm.tried = qualityPayload.tried;
      self.qualityNominationForm.dismissed = qualityPayload.dismissed;
      self.qualityNominationForm.dismissedBeforeAC =
        qualityPayload.dismissedBeforeAC;
      self.qualityNominationForm.canNominateProblem =
        qualityPayload.canNominateProblem;
      self.qualityNominationForm.problemAlias = qualityPayload.problemAlias;
      return;
    }
    self.qualityNominationForm = new Vue({
      el: '#qualitynomination-popup',
      mounted: () => {
        ui.reportEvent('quality-nomination', 'shown');
      },
      render: function(createElement) {
        return createElement('qualitynomination-popup', {
          props: {
            nominated: this.nominated,
            nominatedBeforeAC: this.nominatedBeforeAC,
            solved: this.solved,
            tried: this.tried,
            dismissed: this.dismissed,
            dismissedBeforeAC: this.dismissedBeforeAC,
            canNominateProblem: this.canNominateProblem,
            problemAlias: this.problemAlias,
          },
          on: {
            submit: ev => {
              const contents = {
                before_ac: !ev.solved && ev.tried,
                difficulty:
                  ev.difficulty !== '' ? Number.parseInt(ev.difficulty, 10) : 0,
                tags: ev.tags.length > 0 ? ev.tags : [],
                quality:
                  ev.quality !== '' ? Number.parseInt(ev.quality, 10) : 0,
              };
              api.QualityNomination.create({
                problem_alias: qualityPayload.problemAlias,
                nomination: 'suggestion',
                contents: JSON.stringify(contents),
              })
                .then(() => {
                  ui.reportEvent('quality-nomination', 'submit');
                })
                .catch(ui.apiError);
            },
            dismiss: ev => {
              const contents = {
                before_ac: !ev.solved && ev.tried,
              };
              api.QualityNomination.create({
                problem_alias: qualityPayload.problemAlias,
                nomination: 'dismissal',
                contents: JSON.stringify(contents),
              })
                .then(data => {
                  ui.info(T.qualityNominationRateProblemDesc);
                  ui.reportEvent('quality-nomination', 'dismiss');
                })
                .catch(ui.apiError);
            },
          },
        });
      },
      data: {
        nominated: qualityPayload.nominated,
        nominatedBeforeAC: qualityPayload.nominatedBeforeAC,
        solved: qualityPayload.solved,
        tried: qualityPayload.tried,
        dismissed: qualityPayload.dismissed,
        dismissedBeforeAC: qualityPayload.dismissedBeforeAC,
        canNominateProblem: qualityPayload.canNominateProblem,
        problemAlias: qualityPayload.problemAlias,
      },
      components: {
        'qualitynomination-popup': qualitynomination_Popup,
      },
    });
  }

  renderProblem(problem) {
    let self = this;
    self.currentProblem = problem;
    let statement = document.querySelector('#problem div.statement');
    statement.innerHTML = self.markdownConverter.makeHtmlWithImages(
      problem.statement.markdown,
      problem.statement.images,
      problem.settings,
    );
    const creationDate = document.querySelector(
      '#problem .problem-creation-date',
    );
    if (problem.problemsetter && creationDate) {
      creationDate.innerText = ui.formatString(T.wordsUploadedOn, {
        date: time.formatDate(
          new Date(problem.problemsetter.creation_date * 1000),
        ),
      });
    }

    ui.renderSampleToClipboardButton();

    let libinteractiveInterfaceName = statement.querySelector(
      'span.libinteractive-interface-name',
    );
    if (
      libinteractiveInterfaceName &&
      problem.settings &&
      problem.settings.interactive &&
      problem.settings.interactive.module_name
    ) {
      libinteractiveInterfaceName.innerText = problem.settings.interactive.module_name.replace(
        /\.idl$/,
        '',
      );
    }
    self.installLibinteractiveHooks();
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, statement]);

    self.mountEditor(problem);

    let languageArray = problem.languages;
    self.updateAllowedLanguages(languageArray);
    self.selectDefaultLanguage();

    self.ephemeralGrader.send('setSettings', problem.settings);
  }

  detectShowRun(): void {
    let showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    let showRunMatch = window.location.hash.match(showRunRegex);
    if (showRunMatch) {
      $('#overlay form').hide();
      $('#overlay').show();
      api.Run.details({ run_alias: showRunMatch[1] })
        .then(time.remoteTimeAdapter)
        .then(data => {
          this.displayRunDetails(showRunMatch[1], data);
        })
        .catch(error => {
          ui.apiError(error);
          this.hideOverlay();
        });
    }
  }

  hideOverlay() {
    $('#overlay').hide();
    window.location.hash = window.location.hash.substring(
      0,
      window.location.hash.lastIndexOf('/'),
    );
  }

  bindGlobalHandlers(): void {
    $('#overlay, .close').on('click', (e: Event) => this.onCloseSubmit(e));
    this.elements.submitForm.language.on('change', (e: Event) =>
      this.onLanguageSelect(e),
    );
    this.elements.submitForm.on('submit', (e: Event) => this.onSubmit(e));
  }

  onCloseSubmit(e: Event): void {
    if (
      e.target.id !== 'overlay' &&
      (<JQuery>e.target).closest('button.close') === null
    ) {
      return;
    }
    $('#clarification', this.elements.submitForm).hide();
    this.hideOverlay();
    this.clearInputFile();
    e.preventDefault();
  }

  clearInputFile() {
    let self = this;
    // This worked, nay, was required, on older browsers.
    // It stopped working sometime in 2017, and now .val(null)
    // is enough to clear the input field.
    // Leaving this here for now in case some older browsers
    // still require it.
    self.elements.submitForm.file.replaceWith(
      (self.elements.submitForm.file = self.elements.submitForm.file.clone(
        true,
      )),
    );
    self.elements.submitForm.file.val(null);
  }

  initSubmissionCountdown() {
    let self = this;
    let nextSubmissionTimestamp = new Date(0);
    $('#submit input[type=submit]')
      .removeAttr('value')
      .prop('disabled', false);
    let problem = self.problems[self.currentProblem.alias];
    if (typeof problem !== 'undefined') {
      if (typeof problem.nextSubmissionTimestamp !== 'undefined') {
        nextSubmissionTimestamp = new Date(
          problem.nextSubmissionTimestamp * 1000,
        );
      } else if (
        typeof problem.runs !== 'undefined' &&
        problem.runs.length > 0
      ) {
        nextSubmissionTimestamp = new Date(
          problem.runs[problem.runs.length - 1].time.getTime() +
            self.currentProblemset.submissions_gap * 1000,
        );
      }
    }
    if (self.submissionGapInterval) {
      clearInterval(self.submissionGapInterval);
      self.submissionGapInterval = 0;
    }
    self.submissionGapInterval = setInterval(() => {
      const submissionGapSecondsRemaining = Math.ceil(
        (nextSubmissionTimestamp - Date.now()) / 1000,
      );
      if (submissionGapSecondsRemaining > 0) {
        $('#submit input[type=submit]')
          .attr('disabled', 'disabled')
          .val(
            ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
              submissionGap: submissionGapSecondsRemaining,
            }),
          );
      } else {
        $('#submit input[type=submit]')
          .removeAttr('value')
          .prop('disabled', false);
        clearInterval(self.submissionGapInterval);
      }
    }, 1000);
  }

  onLanguageSelect(e: Event): void {
    const lang = (<HTMLSelectElement>e.target).value;
    let ext = $('.submit-filename-extension', this.elements.submitForm);
    if (lang.startsWith('cpp')) {
      ext.text('.cpp');
    } else if (lang.startsWith('c-')) {
      ext.text('.c');
    } else if (lang.startsWith('py')) {
      ext.text('.py');
    } else if (lang && lang != 'cat') {
      ext.text(`.${lang}`);
    } else {
      ext.text('');
    }
    if (this.codeEditor) {
      this.codeEditor.language = lang;
    }
  }

  onSubmit(e: Event): void {
    e.preventDefault();

    if (
      !this.options.isOnlyProblem &&
      this.problems[this.currentProblem.alias].last_submission +
        this.submissionGap * 1000 >
        Date.now()
    ) {
      alert(
        ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
          submissionGap: this.submissionGap,
        }),
      );
      return;
    }

    let submitForm = this.elements.submitForm;
    let langSelect = this.elements.submitForm.language;
    if (!langSelect.val()) {
      alert(T.arenaRunSubmitMissingLanguage);
      return;
    }

    let file = <HTMLFileInput>this.elements.submitForm.file[0];
    if (file && file.files && file.files.length > 0) {
      file = file.files[0];
      let reader = new FileReader();

      reader.onload = e => {
        this.submitRun(e.target.result);
      };

      let extension = file.name.split(/\./);
      extension = extension[extension.length - 1];

      if (
        langSelect.val() != 'cat' ||
        file.type.indexOf('text/') === 0 ||
        extension == 'cpp' ||
        extension == 'c' ||
        extension == 'cs' ||
        extension == 'java' ||
        extension == 'txt' ||
        extension == 'hs' ||
        extension == 'kp' ||
        extension == 'kj' ||
        extension == 'p' ||
        extension == 'pas' ||
        extension == 'py' ||
        extension == 'rb' ||
        extension == 'lua'
      ) {
        if (file.size >= this.currentProblem.input_limit) {
          alert(
            ui.formatString(T.arenaRunSubmitFilesize, {
              limit: `${this.currentProblem.input_limit / 1024} KiB`,
            }),
          );
          return;
        }
        reader.readAsText(file, 'UTF-8');
      } else {
        // 100kB _must_ be enough for anybody.
        if (file.size >= 100 * 1024) {
          alert(ui.formatString(T.arenaRunSubmitFilesize, { limit: '100kB' }));
          return;
        }
        reader.readAsDataURL(file);
      }

      return;
    }

    if (!this.codeEditor?.code) {
      alert(T.arenaRunSubmitEmptyCode);
      return;
    }
    this.submitRun(this.codeEditor.code);
  }

  computeProblemsetArg(): { contest_alias?: string; problemset_id?: number } {
    if (this.options.isOnlyProblem) {
      return {};
    }
    if (this.options.contestAlias) {
      return { contest_alias: this.options.contestAlias };
    }
    if (this.options.problemsetId) {
      return { problemset_id: this.options.problemsetId };
    }
    return {};
  }

  submitRun(code: string): void {
    let problemset = this.options.isPractice ? {} : this.computeProblemsetArg();
    let lang = this.elements.submitForm.language.val();

    $('input', this.elements.submitForm).attr('disabled', 'disabled');
    api.Run.create(
      $.extend(problemset, {
        problem_alias: this.currentProblem.alias,
        language: lang,
        source: code,
      }),
    )
      .then(response => {
        ui.reportEvent('submission', 'submit');
        if (this.options.isLockdownMode && sessionStorage) {
          sessionStorage.setItem(`run:${response.guid}`, code);
        }

        const currentProblem = this.problems[this.currentProblem.alias];
        if (!this.options.isOnlyProblem) {
          this.problems[this.currentProblem.alias].last_submission = Date.now();
          this.problems[this.currentProblem.alias].nextSubmissionTimestamp =
            response.nextSubmissionTimestamp;
        }
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
          language: <string>(this.elements.submitForm.language.val() ?? ''),
        };
        this.updateRun(run);

        $('input', this.elements.submitForm).prop('disabled', false);
        this.hideOverlay();
        this.clearInputFile();
        this.initSubmissionCountdown();
      })
      .catch(run => {
        alert(run.error ?? run);
        $('input', this.elements.submitForm).prop('disabled', false);
        if (run.errorname) {
          ui.reportEvent('submission', 'submit-fail', run.errorname);
        }
      });
  }

  updateSummary(contest: omegaup.Contest): void {
    this.summaryView.contest = contest;
  }

  displayRunDetails(guid: string, data: messages.RunDetailsResponse): void {
    let problemAdmin = data.admin;

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

    const numericSort: <T extends { [key: string]: any }>(
      key: string,
    ) => (x: T, y: T) => number = (key: string) => {
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
    let detailsGroups = data.details && data.details.groups;
    let groups = undefined;
    if (detailsGroups && detailsGroups.length) {
      detailsGroups.sort(numericSort('group'));
      for (let i = 0; i < detailsGroups.length; i++) {
        if (!detailsGroups[i].cases) {
          continue;
        }
        detailsGroups[i].cases.sort(numericSort('name'));
      }
      groups = detailsGroups;
    }
    if (this.runDetailsView) {
      this.runDetailsView.data = {
        compile_error: data.compile_error,
        logs: data.logs || '',
        judged_by: data.judged_by || '',
        source: sourceHTML,
        source_link: sourceLink,
        source_url: window.URL.createObjectURL(
          new Blob([data.source || ''], { type: 'text/plain' }),
        ),
        source_name: `Main.${data.language}`,
        problem_admin: data.admin,
        guid: data.guid,
        groups: groups,
        language: data.language,
        feedback: <omegaup.SubmissionFeedback>(
          ((this.options.contestAlias && this.currentProblemset?.feedback) ||
            'detailed')
        ),
      };
    }
    (<HTMLElement>document.querySelector('.run-details-view')).style.display =
      'block';
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
    const qualityPayload = (<types.ProblemsetProblem>problem).quality_payload;
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
      this.scoreboard.ranking = this.scoreboard.ranking.map(rank => {
        let ranking = rank;
        if (ranking.username == OmegaUp.username) {
          ranking.problems = rank.problems.map(problem => {
            let problemRanking = problem;
            if (problemRanking.alias == alias) {
              let maxScore = getMaxScore(
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
        problem => problem.alias === alias,
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
