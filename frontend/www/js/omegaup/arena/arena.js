import Vue from 'vue';

import * as api from '../api_transitional';
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
import T from '../lang';
import * as markdown from '../markdown';
import { OmegaUp } from '../omegaup';
import * as time from '../time';
import * as typeahead from '../typeahead';
import * as ui from '../ui';

import ArenaAdmin from './admin_arena.js';
import {
  EphemeralGrader,
  EventsSocket,
  GetOptionsFromLocation,
  myRunsStore,
  runsStore,
} from './arena_transitional';

export { ArenaAdmin, GetOptionsFromLocation };

let ScoreboardColors = [
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

function getMaxScore(runs, alias, previousScore) {
  let maxScore = previousScore;
  for (let run of runs) {
    if (alias != run.alias) {
      continue;
    }
    let score = run.contest_score;
    if (score > maxScore) {
      maxScore = score;
    }
  }
  return maxScore;
}

export class Arena {
  constructor(options) {
    let self = this;

    self.options = options;

    // The current problemset.
    self.currentProblemset = null;

    // The interval for clock updates.
    self.clockInterval = null;

    // The start time of the contest.
    self.startTime = null;

    // The finish time of the contest.
    self.finishTime = null;

    // The deadline for submissions. self might be different from the end time.
    self.submissionDeadline = null;

    // All runs in self contest/problem.
    self.myRunsList = new Vue({
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
            details: run => {
              window.location.hash += `/show-run:${run.guid}`;
            },
          },
        });
      },
      data: {
        isContestFinished: false,
        isProblemsetOpened: false,
        problemAlias: options.isOnlyProblem ? options.onlyProblemAlias : null,
      },
      components: { 'omegaup-arena-runs': arena_Runs },
    });
    const myRunsListElement = document.querySelector('#problem table.runs');
    if (myRunsListElement) {
      self.myRunsList.$mount(myRunsListElement);
    }

    // The guid of any run that is pending.
    self.pendingRuns = {};

    // The set of problems in self contest.
    self.problems = {};

    // WebSocket for real-time updates.
    self.socket = null;

    // The offset of each user into the ranking table.
    self.currentRanking = {};

    // The previous ranking information. Useful to show diffs.
    self.prevRankingState = null;

    // Every time a recent event is shown, have self interval clear it after
    // 30s.
    self.removeRecentEventClassTimeout = null;

    // The last known scoreboard event stream.
    self.currentEvents = null;

    // The Markdown-to-HTML converter.
    self.markdownConverter = markdown.markdownConverter();

    // Currently opened clarification notifications.
    self.commonNavbar = null;
    if (document.getElementById('common-navbar')) {
      self.commonNavbar = new Vue({
        el: '#common-navbar',
        render: function(createElement) {
          return createElement('omegaup-common-navbar', {
            props: {
              omegaUpLockDown: this.omegaUpLockDown,
              inContest: this.inContest,
              isLoggedIn: this.isLoggedIn,
              isReviewer: this.isReviewer,
              gravatarURL51: this.gravatarURL51,
              currentUsername: this.currentUsername,
              isAdmin: this.isAdmin,
              isMainUserIdentity: this.isMainUserIdentity,
              lockDownImage: this.lockDownImage,
              navbarSection: this.navbarSection,
              graderInfo: this.graderInfo,
              graderQueueLength: this.graderQueueLength,
              errorMessage: this.errorMessage,
              initialClarifications: this.initialClarifications,
            },
          });
        },
        data: {
          omegaUpLockDown: self.options.payload.omegaUpLockDown,
          inContest: self.options.payload.inContest,
          isLoggedIn: self.options.payload.isLoggedIn,
          isReviewer: self.options.payload.isReviewer,
          gravatarURL51: self.options.payload.gravatarURL51,
          currentUsername: self.options.payload.currentUsername,
          isAdmin: self.options.payload.isAdmin,
          isMainUserIdentity: self.options.payload.isMainUserIdentity,
          lockDownImage: self.options.payload.lockDownImage,
          navbarSection: self.options.payload.navbarSection,
          graderInfo: null,
          graderQueueLength: -1,
          errorMessage: null,
          initialClarifications: [],
        },
        components: {
          'omegaup-common-navbar': common_Navbar,
        },
      });

      if (self.options.payload.isAdmin) {
        api.Notification.myList({})
          .then(data => {
            self.commonNavbar.notifications = data.notifications;
          })
          .catch(ui.apiError);

        function updateGraderStatus() {
          api.Grader.status()
            .then(stats => {
              self.commonNavbar.graderInfo = stats.grader;
              if (stats.status !== 'ok') {
                self.commonNavbar.errorMessage = T.generalError;
                return;
              }
              if (stats.grader.queue) {
                self.commonNavbar.graderQueueLength =
                  stats.grader.queue.run_queue_length +
                  stats.grader.queue.running.length;
              }
              self.commonNavbar.errorMessage = null;
            })
            .catch(stats => {
              self.commonNavbar.errorMessage = stats.error;
            });
        }

        updateGraderStatus();
        setInterval(updateGraderStatus, 30000);
      }
    }

    // Currently opened problem.
    self.currentProblem = null;

    // If we have admin powers in self contest.
    self.problemsetAdmin = false;
    self.myRunsList.isProblemsetOpened = true;
    self.answeredClarifications = 0;
    self.clarificationsOffset = 0;
    self.clarificationsRowcount = 20;
    self.activeTab = 'problems';
    self.clarifications = {};
    self.submissionGap = 0;

    // Setup preferred language
    self.preferredLanguage = options.preferredLanguage || null;

    // UI elements
    self.elements = {
      clarification: $('#clarification'),
      clock: $('#title .clock'),
      loadingOverlay: $('#loading'),
      ranking: $('#ranking div'),
      socketStatus: $('#title .socket-status'),
      submitForm: $('#submit'),
    };

    if (document.getElementById('arena-navbar-problems') !== null) {
      self.elements.navBar = new Vue({
        el: '#arena-navbar-problems',
        render: function(createElement) {
          return createElement('omegaup-arena-navbar-problems', {
            props: {
              problems: this.problems,
              activeProblem: this.activeProblem,
              inAssignment: this.inAssignment,
            },
            on: {
              'navigate-to-problem': function(problemAlias) {
                window.location.hash = `#problems/${problemAlias}`;
              },
            },
          });
        },
        data: {
          problems: [],
          activeProblem: null,
          inAssignment: !!self.options.courseAlias,
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
      self.elements.miniRanking = new Vue({
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

    if (self.elements.ranking.length) {
      self.elements.rankingTable = new Vue({
        el: self.elements.ranking[0],
        render: function(createElement) {
          return createElement('omegaup-scoreboard', {
            props: {
              scoreboardColors: ScoreboardColors,
              problems: this.problems,
              ranking: this.ranking,
              lastUpdated: this.lastUpdated,
              digitsAfterDecimalPoint: this.digitsAfterDecimalPoint,
            },
          });
        },
        data: {
          problems: [],
          ranking: [],
          lastUpdated: null,
          digitsAfterDecimalPoint: self.digitsAfterDecimalPoint,
        },
        components: {
          'omegaup-scoreboard': arena_Scoreboard,
        },
      });
    }
    $.extend(self.elements.submitForm, {
      code: $('textarea[name="code"]', self.elements.submitForm),
      file: $('input[type="file"]', self.elements.submitForm),
      language: $('select[name="language"]', self.elements.submitForm),
    });

    // Setup run details view, if available.
    if (document.getElementById('run-details') != null) {
      self.runDetailsView = new Vue({
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
    self.bindGlobalHandlers();

    // Contest summary view model
    self.summaryView = new Vue({
      render: function(createElement) {
        return createElement('omegaup-arena-contestsummary', {
          props: {
            contest: this.contest,
            showRanking: !self.options.isPractice,
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
      self.summaryView.$mount(summaryElement);
    }

    // The interval of time that submissions button will be disabled
    self.submissionGapInterval = 0;

    // Cache scoreboard data for virtual contest
    self.originalContestScoreboardEvent = null;

    // Virtual contest refresh interval
    self.virtualContestRefreshInterval = null;

    // Ephemeral grader support.
    self.ephemeralGrader = new EphemeralGrader();

    // Number of digits after the decimal point to show.
    self.digitsAfterDecimalPoint = 2;

    self.qualityNominationForm = null;

    self.elements.assignmentsNav = null;
  }

  installLibinteractiveHooks() {
    let self = this;
    $('.libinteractive-download form').on('submit', function(e) {
      let form = $(e.target);
      e.preventDefault();
      let alias = self.currentProblem.alias;
      let commit = self.currentProblem.commit;
      let os = form.find('.download-os').val();
      let lang = form.find('.download-lang').val();
      let extension = os == 'unix' ? '.tar.bz2' : '.zip';

      ui.navigateTo(
        window.location.protocol +
          '//' +
          window.location.host +
          `/templates/${alias}/${commit}/${alias}_${os}_${lang}${extension}`,
      );

      return false;
    });

    $('.libinteractive-download .download-lang').on('change', function(e) {
      var form = e.target;
      while (!form.classList.contains('libinteractive-download')) {
        form = form.parentElement;
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

    function connect(uris, index) {
      self.socket = new EventsSocket(uris[index], self);
      self.socket.connect().catch(function(e) {
        console.log(e);
        // Try the next uri.
        index++;
        if (index < uris.length) {
          connect(uris, index);
        } else {
          // Out of options. Falling back to polls.
          self.socket = null;
          setTimeout(function() {
            self.setupPolls();
          }, Math.random() * 15000);
        }
      });
    }

    self.elements.socketStatus.html('↻').css('color', '#888');
    connect(uris, 0, 10);
  }

  setupPolls() {
    let self = this;
    self.refreshRanking();
    if (!self.options.contestAlias) {
      return;
    }
    self.refreshClarifications();

    if (!self.socket) {
      self.clarificationInterval = setInterval(function() {
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

  initClock(start, finish, deadline) {
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

  problemsetLoaded(problemset) {
    let self = this;
    if (problemset.status == 'error') {
      if (!OmegaUp.loggedIn) {
        window.location = '/login/?redirect=' + escape(window.location);
      } else if (problemset.start_time) {
        let f = (function(x, y) {
          return function() {
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

      if (self.elements.navBar) {
        self.elements.navBar.problems.push({
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
      self.elements.assignmentsNav === null
    ) {
      self.elements.assignmentsNav = new Vue({
        el: '#arena-navbar-assignments',
        render: function(createElement) {
          return createElement('omegaup-arena-navbar-assignments', {
            props: {
              assignments: this.assignments,
              currentAssignmentAlias: this.currentAssignmentAlias,
            },
            on: {
              'navigate-to-assignment': function(assignmentAlias) {
                window.location.pathname = `/course/${self.options.courseAlias}/assignment/${assignmentAlias}/`;
              },
            },
          });
        },
        data: {
          assignments: problemset.courseAssignments,
          currentAssignmentAlias: problemset.alias,
        },
        components: {
          'omegaup-arena-navbar-assignments': arena_Navbar_Assignments,
        },
      });
    }
  }

  initProblems(problemset) {
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
    if (self.elements.rankingTable) {
      self.elements.rankingTable.problems = problems;
      self.elements.rankingTable.showPenalty = problemset.show_penalty;
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

  updateRunFallback(guid) {
    let self = this;
    if (self.socket != null) return;
    setTimeout(function() {
      api.Run.status({ run_alias: guid })
        .then(time.remoteTimeAdapter)
        .then(self.updateRun.bind(self))
        .catch(ui.ignoreError);
    }, 5000);
  }

  updateRun(run) {
    let self = this;

    self.trackRun(run);

    if (self.socket != null) return;

    if (run.status == 'ready') {
      if (
        !self.options.isPractice &&
        !self.options.isOnlyProblem &&
        self.options.contestAlias != 'admin'
      ) {
        self.refreshRanking();
      }
    } else {
      self.updateRunFallback(run.guid);
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
        .then(function(response) {
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
        .then(self.rankingChange.bind(self))
        .catch(ui.ignoreError);
    }
  }

  onVirtualRankingChange(virtualContestData) {
    let self = this;
    // This clones virtualContestData to data so that virtualContestData values
    // won't be overriden by processes below
    let data = JSON.parse(JSON.stringify(virtualContestData));
    let events = self.originalContestScoreboardEvent;
    let currentDelta =
      (new Date().getTime() - self.startTime.getTime()) / (1000 * 60);

    for (let rank of data.ranking) rank.virtual = true;

    let problemOrder = {};
    let problems = [];
    let initialProblems = [];

    for (let problem of Object.values(self.problems)) {
      problemOrder[problem.alias] = problems.length;
      initialProblems.push({
        penalty: 0,
        percent: 0,
        points: 0,
        runs: 0,
      });
      problems.push({ order: problems.length + 1, alias: problem.alias });
    }

    // Calculate original contest scoreboard with current delta time
    let originalContestRanking = {};
    let originalContestEvents = [];

    // Refresh after time T
    let refreshTime = 30 * 1000; // 30 seconds

    events.forEach(function(evt) {
      let key = evt.username;
      if (!originalContestRanking.hasOwnProperty(key)) {
        originalContestRanking[key] = {
          country: evt.country,
          name: evt.name,
          username: evt.username,
          problems: Array.from(initialProblems),
          total: {
            penalty: 0,
            points: 0,
            runs: 0,
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
      let problem =
        originalContestRanking[key].problems[problemOrder[evt.problem.alias]];
      originalContestRanking[key].problems[problemOrder[evt.problem.alias]] = {
        penalty: evt.problem.penalty,
        points: evt.problem.points,
        runs: problem ? problem.runs + 1 : 1, // If problem appeared in event for than one, it
        // means a problem has been solved multiple times
      };
      originalContestRanking[key].total = evt.total;
    });
    // Merge original contest scoreboard ranking with virtual contest
    for (let ranking of Object.values(originalContestRanking))
      data.ranking.push(ranking);

    // Re-sort rank
    data.ranking.sort((rank1, rank2) => {
      return rank2.total.points - rank1.total.points;
    });

    // Override ranking
    data.ranking.forEach((rank, index) => (rank.place = index + 1));
    self.onRankingChanged(data);

    let scoreboardEventsParams = {
      problemset_id: self.options.problemsetId,
    };
    if (self.options.scoreboardToken) {
      scoreboardEventsParams.token = self.options.scoreboardToken;
    }

    api.Problemset.scoreboardEvents(scoreboardEventsParams)
      .then(function(response) {
        // Change username to username-virtual
        for (let evt of response.events) {
          evt.username = ui.formatString(T.virtualSuffix, {
            username: evt.username,
          });
          evt.name = ui.formatString(T.virtualSuffix, { username: evt.name });
        }

        // Merge original contest and virtual contest scoreboard events
        response.events = response.events.concat(originalContestEvents);
        self.onRankingEvents(response);
      })
      .catch(ui.ignoreError);

    self.virtualContestRefreshInterval = setTimeout(function() {
      self.onVirtualRankingChange(virtualContestData);
    }, refreshTime);
  }

  virtualRankingChange(data) {
    // Merge original contest scoreboard and virtual contest
    let self = this;

    // Stop existing scoreboard simulation
    if (self.virtualContestRefreshInterval != null)
      clearTimeout(self.virtualContestRefreshInterval);

    if (self.originalContestScoreboardEvent == null) {
      api.Problemset.scoreboardEvents({
        problemset_id: self.options.originalProblemsetId,
      })
        .then(function(response) {
          self.originalContestScoreboardEvent = response.events;
          self.onVirtualRankingChange(data);
        })
        .catch(ui.apiError);
    } else {
      self.onVirtualRankingChange(data);
    }
  }

  rankingChange(data, rankingEvent = true) {
    let self = this;
    self.onRankingChanged(data);
    let scoreboardEventsParams = {
      problemset_id:
        self.options.problemsetId || self.currentProblemset.problemset_id,
    };
    if (self.options.scoreboardToken) {
      scoreboardEventsParams.token = self.options.scoreboardToken;
    }

    if (rankingEvent) {
      api.Problemset.scoreboardEvents(scoreboardEventsParams)
        .then(self.onRankingEvents.bind(self))
        .catch(ui.ignoreError);
    }
  }

  onRankingChanged(data) {
    let self = this;
    if (typeof self.elements.miniRanking !== 'undefined') {
      self.elements.miniRanking.users = [];
    }

    if (self.removeRecentEventClassTimeout) {
      clearTimeout(self.removeRecentEventClassTimeout);
      self.removeRecentEventClassTimeout = null;
    }

    let ranking = data.ranking || [];
    let newRanking = {};
    let order = {};
    let currentRankingState = {};

    for (let i = 0; i < data.problems.length; i++) {
      order[data.problems[i].alias] = i;
    }

    // Push data to ranking table
    for (let i = 0; i < ranking.length; i++) {
      let rank = ranking[i];
      newRanking[rank.username] = i;

      let username = ui.rankingUsername(rank);
      currentRankingState[username] = { place: rank.place, accepted: {} };

      // Update problem scores.
      let totalRuns = 0;
      for (let alias in order) {
        if (!order.hasOwnProperty(alias)) continue;
        let problem = rank.problems[order[alias]];
        totalRuns += problem.runs;

        if (
          self.problems[alias] &&
          rank.username == OmegaUp.username &&
          self.problems[alias].languages !== ''
        ) {
          const currentPoints = parseFloat(self.problems[alias].points || '0');
          if (self.elements.navBar) {
            const currentProblem = self.elements.navBar.problems.find(
              problem => problem.alias === alias,
            );
            currentProblem.bestScore = problem.points;
            currentProblem.maxScore = currentPoints;
          }
          self.updateProblemScore(alias, currentPoints, problem.points);
        }
      }

      // update miniranking
      if (i < 10) {
        if (typeof self.elements.miniRanking !== 'undefined') {
          const username = ui.rankingUsername(rank);
          self.elements.miniRanking.users.push({
            position: rank.place,
            username: username,
            country: rank['country'],
            classname: rank['classname'],
            points: rank.total.points,
            penalty: rank.total.penalty,
          });
        }
      }
    }

    if (self.elements.rankingTable) {
      self.elements.rankingTable.ranking = ranking;
      if (data.time) {
        self.elements.rankingTable.lastUpdated = OmegaUp.remoteTime(data.time);
      }
    }

    this.currentRanking = newRanking;
    this.prevRankingState = currentRankingState;
    self.removeRecentEventClassTimeout = setTimeout(function() {
      $('.recent-event').removeClass('recent-event');
    }, 30000);
  }

  onRankingEvents(data) {
    let dataInSeries = {};
    let navigatorData = [[this.startTime.getTime(), 0]];
    let series = [];
    let usernames = {};

    // Don't trust input data (data might not be sorted)
    data.events.sort((a, b) => a.delta - b.delta);

    this.currentEvents = data;
    // group points by person
    for (let i = 0, l = data.events.length; i < l; i++) {
      let curr = data.events[i];

      // limit chart to top n users
      if (this.currentRanking[curr.username] > ScoreboardColors.length - 1)
        continue;

      if (!dataInSeries[curr.name]) {
        dataInSeries[curr.name] = [[this.startTime.getTime(), 0]];
        usernames[curr.name] = curr.username;
      }
      dataInSeries[curr.name].push([
        this.startTime.getTime() + curr.delta * 60 * 1000,
        curr.total.points,
      ]);

      // check if to add to navigator
      if (curr.total.points > navigatorData[navigatorData.length - 1][1]) {
        navigatorData.push([
          this.startTime.getTime() + curr.delta * 60 * 1000,
          curr.total.points,
        ]);
      }
    }

    // convert datas to series
    for (let i in dataInSeries) {
      if (dataInSeries.hasOwnProperty(i)) {
        dataInSeries[i].push([
          this.finishTime
            ? Math.min(this.finishTime.getTime(), Date.now())
            : Date.now(),
          dataInSeries[i][dataInSeries[i].length - 1][1],
        ]);
        series.push({
          name: i,
          rank: this.currentRanking[usernames[i]],
          data: dataInSeries[i],
          step: true,
        });
      }
    }

    series.sort(function(a, b) {
      return a.ranking - b.ranking;
    });

    navigatorData.push([
      this.finishTime
        ? Math.min(this.finishTime.getTime(), Date.now())
        : Date.now(),
      navigatorData[navigatorData.length - 1][1],
    ]);
    this.createChart(series, navigatorData);
  }

  createChart(series, navigatorSeries) {
    let self = this;
    if (series.length == 0 || self.elements.ranking.length == 0) return;

    Highcharts.setOptions({ colors: ScoreboardColors });

    window.chart = new Highcharts.StockChart({
      chart: { renderTo: 'ranking-chart', height: 300, spacingTop: 20 },

      xAxis: {
        ordinal: false,
        min: self.startTime.getTime(),
        max: Math.min(self.finishTime.getTime(), Date.now()),
      },

      yAxis: {
        showLastLabel: true,
        showFirstLabel: false,
        min: 0,
        max: (function(problems) {
          let total = 0;
          for (let prob in problems) {
            if (!problems.hasOwnProperty(prob)) continue;
            total += parseInt(problems[prob].points, 10);
          }
          return total;
        })(self.problems),
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
    });
  }

  refreshClarifications() {
    let self = this;
    api.Contest.clarifications({
      contest_alias: self.options.contestAlias,
      offset: self.clarificationsOffset,
      rowcount: self.clarificationsRowcount,
    })
      .then(time.remoteTimeAdapter)
      .then(self.clarificationsChange.bind(self))
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
        (function(id, answerNode) {
          let responseFormNode = $(
            '#create-response-form',
            answerNode,
          ).removeClass('template');
          let cannedResponse = $('#create-response-canned', answerNode);
          cannedResponse.on('change', function() {
            if (cannedResponse.val() === 'other') {
              $('#create-response-text', answerNode).show();
            } else {
              $('#create-response-text', answerNode).hide();
            }
          });
          if (clarification.public == 1) {
            $('#create-response-is-public', responseFormNode).attr(
              'checked',
              'checked',
            );
            $('#create-response-is-public', responseFormNode).prop(
              'checked',
              true,
            );
          }
          responseFormNode.on('submit', function() {
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
              .then(function() {
                $('pre', answerNode).html(responseText);
                $('#create-response-text', answerNode).val('');
              })
              .catch(function() {
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
    if (clarification.public == 1) {
      $('#create-response-is-public', r).prop('checked', true);
    }
  }

  clarificationsChange(data) {
    let self = this;
    if (data.status != 'ok') {
      return;
    }
    $('.clarifications tr.inserted').remove();
    if (
      data.clarifications.length > 0 &&
      data.clarifications.length < self.clarificationsRowcount
    ) {
      $('#clarifications-count').html(`(${data.clarifications.length})`);
    } else if (data.clarifications.length >= self.clarificationsRowcount) {
      $('#clarifications-count').html(`(${data.clarifications.length}+)`);
    }

    let previouslyAnswered = self.answeredClarifications;
    self.answeredClarifications = 0;
    self.clarifications = {};

    for (let i = data.clarifications.length - 1; i >= 0; i--) {
      self.updateClarification(data.clarifications[i]);
    }

    if (self.commonNavbar !== null) {
      self.commonNavbar.initialClarifications = data.clarifications
        .filter(clarification =>
          // Removing all unsolved clarifications.
          self.problemsetAdmin
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
      self.answeredClarifications > previouslyAnswered &&
      self.activeTab != 'clarifications'
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
      $('option', langElement).each(function() {
        let option = $(this);
        if (option.val() != self.preferredLanguage) return;
        option.prop('selected', true);
        return false;
      });
    }
    if (langElement.val()) return;

    $('option', langElement).each(function() {
      let option = $(this);

      option.prop('selected', true);
      langElement.trigger('change');
      return false;
    });
  }

  mountEditor(problem) {
    let self = this;
    let lang = self.elements.submitForm.language.val();
    let template = '';
    if (problem.templates && lang && problem.templates[lang]) {
      template = problem.templates[lang];
    }
    if (self.codeEditor) {
      self.codeEditor.code = template;
      return;
    }

    self.codeEditor = new Vue({
      el: self.elements.submitForm.code[0],
      data: {
        language: lang,
        code: template,
      },
      methods: {
        refresh: function() {
          // It's possible for codeMirror not to have been set yet
          // if this method is used before the mounted event handler
          // is called.
          if (this.codeMirror) {
            this.codeMirror.refresh();
          }
        },
      },
      mounted: function() {
        let self = this;
        // Wait for sub-components to be mounted...
        this.$nextTick(() => {
          // ... and then fish out a reference to the wrapped
          // CodeMirror instance.
          //
          // The full path is:
          // - self: this unnamed component
          // - $children[0]: CodeView instance
          // - $refs['cm-wrapper']: vue-codemirror instance
          // - editor: the actual CodeMirror instance
          self.codeMirror = self.$children[0].$refs['cm-wrapper'].editor;
        });
      },
      render: function(createElement) {
        return createElement('omegaup-arena-code-view', {
          props: {
            language: this.language,
            value: this.code,
          },
          on: {
            input: value => {
              this.code = value;
            },
            change: value => {
              this.code = value;
            },
          },
        });
      },
      components: {
        'omegaup-arena-code-view': arena_CodeView,
      },
    });
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
      if (self.elements.navBar) {
        self.elements.navBar.activeProblem = self.currentProblem.alias;
      }

      function update(problem) {
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
        if (
          karel_langs.every(function(x) {
            return problem.languages.indexOf(x) != -1;
          })
        ) {
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

        function updateRuns(runs) {
          if (runs) {
            for (let run of runs) {
              self.trackRun(run);
            }
          }
          self.myRunsList.problemAlias = problem.alias;
        }

        function showQualityNominationPopup() {
          let qualityPayload = self.currentProblem.quality_payload;
          if (typeof qualityPayload === 'undefined') {
            // Quality Nomination only works for Courses
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
            self.qualityNominationForm.problemAlias =
              qualityPayload.problemAlias;
            return;
          }
          self.qualityNominationForm = new Vue({
            el: '#qualitynomination-popup',
            mounted: function() {
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
                  submit: function(ev) {
                    const contents = {
                      before_ac: !ev.solved && ev.tried,
                      difficulty:
                        ev.difficulty !== ''
                          ? Number.parseInt(ev.difficulty, 10)
                          : 0,
                      tags: ev.tags.length > 0 ? ev.tags : [],
                      quality:
                        ev.quality !== '' ? Number.parseInt(ev.quality, 10) : 0,
                    };
                    api.QualityNomination.create({
                      problem_alias: qualityPayload.problem_alias,
                      nomination: 'suggestion',
                      contents: JSON.stringify(contents),
                    })
                      .then(() => {
                        ui.reportEvent('quality-nomination', 'submit');
                      })
                      .catch(ui.apiError);
                  },
                  dismiss: function(ev) {
                    const contents = {
                      before_ac: !ev.solved && ev.tried,
                    };
                    api.QualityNomination.create({
                      problem_alias: qualityPayload.problem_alias,
                      nomination: 'dismissal',
                      contents: JSON.stringify(contents),
                    })
                      .then(function(data) {
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
              canNominateProblem: qualityPayload.can_nominate_problem,
              problemAlias: qualityPayload.problem_alias,
            },
            components: {
              'qualitynomination-popup': qualitynomination_Popup,
            },
          });
        }

        if (self.options.isPractice || self.options.isOnlyProblem) {
          api.Problem.runs({ problem_alias: problem.alias })
            .then(time.remoteTimeAdapter)
            .then(function(data) {
              updateRuns(data.runs);
            })
            .catch(ui.apiError);
        } else {
          updateRuns(problem.runs);
          showQualityNominationPopup();
        }

        self.initSubmissionCountdown();
      }

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
            .then(function(problem_ext) {
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
      if (self.elements.navBar) {
        self.elements.navBar.activeProblem = null;
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

  detectShowRun() {
    let self = this;
    let showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    let showRunMatch = window.location.hash.match(showRunRegex);
    if (showRunMatch) {
      $('#overlay form').hide();
      $('#overlay').show();
      api.Run.details({ run_alias: showRunMatch[1] })
        .then(time.remoteTimeAdapter)
        .then(function(data) {
          self.displayRunDetails(showRunMatch[1], data);
        })
        .catch(ui.apiError);
    }
  }

  hideOverlay() {
    $('#overlay').hide();
    window.location.hash = window.location.hash.substring(
      0,
      window.location.hash.lastIndexOf('/'),
    );
  }

  bindGlobalHandlers() {
    let self = this;
    $('#overlay, .close').on('click', self.onCloseSubmit.bind(self));
    self.elements.submitForm.language.on(
      'change',
      self.onLanguageSelect.bind(self),
    );
    self.elements.submitForm.on('submit', self.onSubmit.bind(self));
  }

  onCloseSubmit(e) {
    let self = this;
    if (
      e.target.id !== 'overlay' &&
      e.target.closest('button.close') === null
    ) {
      return;
    }
    $('#clarification', self.elements.submitForm).hide();
    self.hideOverlay();
    self.clearInputFile();
    return false;
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
    self.submissionGapInterval = setInterval(function() {
      let submissionGapSecondsRemaining = Math.ceil(
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

  onLanguageSelect(e) {
    let self = this;
    let lang = $(e.target).val();
    let ext = $('.submit-filename-extension', self.elements.submitForm);
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
    if (self.codeEditor) {
      self.codeEditor.language = lang;
    }
  }

  onSubmit(e) {
    let self = this;
    e.preventDefault();

    if (
      !self.options.isOnlyProblem &&
      self.problems[self.currentProblem.alias].last_submission +
        self.submissionGap * 1000 >
        Date.now()
    ) {
      alert(
        ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
          submissionGap: self.submissionGap,
        }),
      );
      return false;
    }

    let submitForm = self.elements.submitForm;
    let langSelect = self.elements.submitForm.language;
    if (!langSelect.val()) {
      alert(T.arenaRunSubmitMissingLanguage);
      return false;
    }

    let file = self.elements.submitForm.file[0];
    if (file && file.files && file.files.length > 0) {
      file = file.files[0];
      let reader = new FileReader();

      reader.onload = function(e) {
        self.submitRun(e.target.result);
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
        if (file.size >= self.currentProblem.input_limit) {
          alert(
            ui.formatString(T.arenaRunSubmitFilesize, {
              limit: `${self.currentProblem.input_limit / 1024} KiB`,
            }),
          );
          return false;
        }
        reader.readAsText(file, 'UTF-8');
      } else {
        // 100kB _must_ be enough for anybody.
        if (file.size >= 100 * 1024) {
          alert(ui.formatString(T.arenaRunSubmitFilesize, { limit: '100kB' }));
          return false;
        }
        reader.readAsDataURL(file);
      }

      return false;
    }

    if (!self.codeEditor.code) {
      alert(T.arenaRunSubmitEmptyCode);
      return false;
    }
    self.submitRun(self.codeEditor.code);

    return false;
  }

  computeProblemsetArg() {
    let self = this;
    if (self.options.isOnlyProblem) {
      return {};
    }
    if (self.options.contestAlias) {
      return { contest_alias: self.options.contestAlias };
    }
    return { problemset_id: self.options.problemsetId };
  }

  submitRun(code) {
    let self = this;
    let problemset = self.options.isPractice ? {} : self.computeProblemsetArg();
    let lang = self.elements.submitForm.language.val();

    $('input', self.elements.submitForm).attr('disabled', 'disabled');
    api.Run.create(
      $.extend(problemset, {
        problem_alias: self.currentProblem.alias,
        language: lang,
        source: code,
      }),
    )
      .then(function(run) {
        ui.reportEvent('submission', 'submit');
        if (self.options.isLockdownMode && sessionStorage) {
          sessionStorage.setItem(`run:${run.guid}`, code);
        }

        if (!self.options.isOnlyProblem) {
          self.problems[self.currentProblem.alias].last_submission = Date.now();
          self.problems[self.currentProblem.alias].nextSubmissionTimestamp =
            run.nextSubmissionTimestamp;
        }
        run.username = OmegaUp.username;
        run.status = 'new';
        run.alias = self.currentProblem.alias;
        run.contest_score = null;
        run.time = new Date();
        run.penalty = 0;
        run.runtime = 0;
        run.memory = 0;
        run.language = self.elements.submitForm.language.val();
        self.updateRun(run);

        $('input', self.elements.submitForm).prop('disabled', false);
        self.hideOverlay();
        self.clearInputFile();
        self.initSubmissionCountdown();
      })
      .catch(function(run) {
        alert(run.error);
        $('input', self.elements.submitForm).prop('disabled', false);
        ui.reportEvent('submission', 'submit-fail', run.errorname);
      });
  }

  updateSummary(contest) {
    this.summaryView.contest = contest;
  }

  displayRunDetails(guid, data) {
    let self = this;
    let problemAdmin = data.admin;
    if (data.status == 'error') {
      self.hideOverlay();
      return;
    }

    let sourceHTML,
      sourceLink = false;
    if (data.source.indexOf('data:') === 0) {
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

    function numericSort(key) {
      function isDigit(x) {
        return '0' <= x && x <= '9';
      }
      return function(x, y) {
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
    }
    let detailsGroups = data.details && data.details.groups;
    let groups = null;
    if (detailsGroups && detailsGroups.length) {
      detailsGroups.sort(numericSort('group'));
      for (let i = 0; i < detailsGroups.length; i++) {
        detailsGroups[i].cases.sort(numericSort('name'));
      }
      groups = detailsGroups;
    }
    self.runDetailsView.data = {
      compile_error: data.compile_error,
      logs: data.logs,
      judged_by: data.judged_by,
      source: sourceHTML,
      source_link: sourceLink,
      source_url: window.URL.createObjectURL(
        new Blob([data.source], { type: 'text/plain' }),
      ),
      source_name: `Main.${data.language}`,
      problem_admin: data.admin,
      guid: data.guid,
      groups: groups,
      language: data.language,
    };
    document.querySelector('.run-details-view').style.display = 'block';
  }

  trackRun(run) {
    let self = this;
    runsStore.commit('addRun', run);
    if (run.username == OmegaUp.username) {
      myRunsStore.commit('addRun', run);
      if (typeof self.problems[run.alias] != 'undefined') {
        self.updateProblemScore(run.alias, self.problems[run.alias].points, 0);
      }
    }
  }

  updateProblemScore(alias, maxScore, previousScore) {
    let self = this;
    // It only works for contests
    if (
      self.options.contestAlias != null &&
      typeof self.elements.rankingTable !== 'undefined'
    ) {
      self.elements.rankingTable.ranking = self.elements.rankingTable.ranking.map(
        rank => {
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
        },
      );
    }
    if (self.elements.navBar) {
      const currentProblem = self.elements.navBar.problems.find(
        problem => problem.alias === alias,
      );
      currentProblem.bestScore = getMaxScore(
        myRunsStore.state.runs,
        alias,
        previousScore,
      );
      currentProblem.maxScore = maxScore || '0';
    }
  }
}
