import {OmegaUp, T} from '../omegaup.js';
import API from '../api.js';
import ArenaAdmin from './admin_arena.js';
import Notifications from './notifications.js';
import arena_CodeView from '../components/arena/CodeView.vue';
import arena_Scoreboard from '../components/arena/Scoreboard.vue';
import UI from '../ui.js';
import Vue from 'vue';

export {ArenaAdmin};

export function FormatDelta(delta) {
  let days = Math.floor(delta / (24 * 60 * 60 * 1000));
  delta -= days * (24 * 60 * 60 * 1000);
  let hours = Math.floor(delta / (60 * 60 * 1000));
  delta -= hours * (60 * 60 * 1000);
  let minutes = Math.floor(delta / (60 * 1000));
  delta -= minutes * (60 * 1000);
  let seconds = Math.floor(delta / 1000);

  let clock = '';

  if (days > 0) {
    clock += days + ':';
  }
  if (hours < 10) clock += '0';
  clock += hours + ':';
  if (minutes < 10) clock += '0';
  clock += minutes + ':';
  if (seconds < 10) clock += '0';
  clock += seconds;

  return clock;
}

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

export function GetOptionsFromLocation(arenaLocation) {
  let options = {
    isLockdownMode: false,
    isInterview: false,
    isPractice: false,
    isOnlyProblem: false,
    disableClarifications: false,
    disableSockets: false,
    contestAlias: null,
    scoreboardToken: null
  };

  if ($('body').hasClass('lockdown')) {
    options.isLockdownMode = true;
    window.onbeforeunload = function(e) {
      let dialogText = T.lockdownMessageWarning;
      e.returnValue = dialogText;
      return e.returnValue;
    };
  }

  if (arenaLocation.pathname.indexOf('/practice') !== -1) {
    options.isPractice = true;
  }

  if (arenaLocation.pathname.indexOf('/arena/problem/') !== -1) {
    options.isOnlyProblem = true;
    options.onlyProblemAlias =
        /\/arena\/problem\/([^\/]+)\/?/.exec(arenaLocation.pathname)[1];
  } else {
    let match = /\/arena\/([^\/]+)\/?/.exec(arenaLocation.pathname);
    if (match) {
      options.contestAlias = match[1];
    }
  }

  if (arenaLocation.search.indexOf('ws=off') !== -1) {
    options.disableSockets = true;
  }

  return options;
}

class EventsSocket {
  constructor(uri, arena) {
    let self = this;

    self.uri = uri;
    self.arena = arena;
    self.socket = null;
    self.socketKeepalive = null;
    self.deferred = $.Deferred();
    self.retries = 10;
  }

  connect() {
    let self = this;

    self.shouldRetry = false;
    try {
      self.socket = new WebSocket(self.uri, 'com.omegaup.events');
    } catch (e) {
      self.onclose(e);
      return;
    }

    self.socket.onmessage = self.onmessage.bind(self);
    self.socket.onopen = self.onopen.bind(self);
    self.socket.onclose = self.onclose.bind(self);

    return self.deferred;
  }

  onmessage(message) {
    let self = this;
    let data = JSON.parse(message.data);

    if (data.message == '/run/update/') {
      data.run.time = OmegaUp.remoteTime(data.run.time * 1000);
      self.arena.updateRun(data.run);
    } else if (data.message == '/clarification/update/') {
      if (!self.arena.options.disableClarifications) {
        data.clarification.time =
            OmegaUp.remoteTime(data.clarification.time * 1000);
        self.arena.updateClarification(data.clarification);
      }
    } else if (data.message == '/scoreboard/update/') {
      if (self.arena.contestAdmin && data.scoreboard_type != 'admin') return;
      self.arena.rankingChange(data.scoreboard);
    }
  }

  onopen() {
    let self = this;
    self.shouldRetry = true;
    self.arena.elements.socketStatus.html('&bull;').css('color', '#080');
    self.socketKeepalive =
        setInterval(function() { self.socket.send('"ping"'); }, 30000);
  }

  onclose(e) {
    let self = this;
    self.socket = null;
    if (self.socketKeepalive) {
      clearInterval(self.socketKeepalive);
      self.socketKepalive = null;
    }
    if (self.shouldRetry && self.retries > 0) {
      self.retries--;
      self.arena.elements.socketStatus.html('↻').css('color', '#888');
      setTimeout(self.connect.bind(self), Math.random() * 15000);
      return;
    }

    self.arena.elements.socketStatus.html('✗').css('color', '#800');
    self.deferred.reject(e);
  }
}
;

export class Arena {
  constructor(options) {
    let self = this;

    self.options = options;

    // The current contest.
    self.currentContest = null;

    // The interval for clock updates.
    self.clockInterval = null;

    // The start time of the contest.
    self.startTime = null;

    // The finish time of the contest.
    self.finishTime = null;

    // The deadline for submissions. self might be different from the end time.
    self.submissionDeadline = null;

    // All runs in self contest/problem.
    self.runs = new RunView(self);
    self.myRuns = new RunView(self);
    self.myRuns.filter_username(OmegaUp.username);

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

    // Currently opened notifications.
    self.notifications = new Notifications();
    OmegaUp.on('ready',
               function() { self.notifications.attach($('#notifications')); });

    // Currently opened problem.
    self.currentProblem = null;

    // If we have admin powers in self contest.
    self.contestAdmin = false;
    self.answeredClarifications = 0;
    self.clarificationsOffset = 0;
    self.clarificationsRowcount = 20;
    self.activeTab = 'problems';
    self.clarifications = {};
    self.submissionGap = 0;

    // Setup preferred language
    self.preferredLanguage = null;

    // UI elements
    self.elements = {
      clarification: $('#clarification'),
      clock: $('#title .clock'),
      loadingOverlay: $('#loading'),
      miniRanking: $('#mini-ranking'),
      problemList: $('#problem-list'),
      rankingTable: new Vue({
        el: '#ranking div',
        render: function(createElement) {
          return createElement('omegaup-scoreboard', {
            props: {
              T: T,
              scoreboardColors: ScoreboardColors,
              problems: this.problems,
              ranking: this.ranking,
              lastUpdated: this.lastUpdated,
            },
          });
        },
        data: {
          problems: [],
          ranking: [],
          lastUpdated: null,
        },
        components: {
          'omegaup-scoreboard': arena_Scoreboard,
        },
      }),
      socketStatus: $('#title .socket-status'),
      submitForm: $('#submit'),
    };
    $.extend(self.elements.submitForm, {
      code: $('textarea[name="code"]', self.elements.submitForm),
      file: $('input[type="file"]', self.elements.submitForm),
      language: $('select[name="language"]', self.elements.submitForm),
    });

    // Setup any global hooks.
    self.installLibinteractiveHooks();
    self.bindGlobalHandlers();

    // Contest summary view model
    self.summaryView = {
      title: ko.observable(),
      description: ko.observable(),
      windowLength: ko.observable(),
      contestOrganizer: ko.observable(),
      startTime: ko.observable(),
      finishTime: ko.observable(),
      scoreboardCutoff: ko.observable(),
      attached: false,
    };

    // The interval of time that submissions button will be disabled
    self.submissionGapInterval = 0;
  }

  installLibinteractiveHooks() {
    let self = this;
    $('#libinteractive-download')
        .on('submit', function(e) {
          let form = $(e.target);
          let alias = e.target.attributes['data-alias'].value;
          let os = form.find('.download-os').val();
          let lang = form.find('.download-lang').val();
          let extension = (os == 'unix' ? '.tar.bz2' : '.zip');

          UI.navigateTo(window.location.protocol + '//' + window.location.host +
                        '/templates/' + alias + '/' + alias + '_' + os + '_' +
                        lang + extension);

          return false;
        });

    $('#libinteractive-download .download-lang')
        .on('change', function(e) {
          let form = $('#libinteractive-download');
          form.find('.libinteractive-extension')
              .html(form.find('.download-lang').val());
        });
  }

  connectSocket() {
    let self = this;
    if (self.options.isPractice || self.options.disableSockets ||
        self.options.contestAlias == 'admin') {
      self.elements.socketStatus.html('✗').css('color', '#800');
      return false;
    }

    let protocol = (window.location.protocol === 'https:') ? 'wss://' : 'ws://';
    let uris = [];
    // Backendv2 uri
    uris.push(protocol + window.location.host + '/events/?filter=/contest/' +
              self.options.contestAlias +
              (self.options.scoreboardToken ?
                   ('/' + self.options.scoreboardToken) :
                   ''));
    // Legacy uri
    // TODO(lhchavez): Remove once we migrate to backendv2
    uris.push(protocol + window.location.host + '/api/contest/events/' +
              self.options.contestAlias + '/' +
              (self.options.scoreboardToken ?
                   ('?token=' + self.options.scoreboardToken) :
                   ''));

    function connect(uris, index) {
      self.socket = new EventsSocket(uris[index], self);
      self.socket.connect().fail(function(e) {
        console.log(e);
        // Try the next uri.
        index++;
        if (index < uris.length) {
          connect(uris, index);
        } else {
          // Out of options. Falling back to polls.
          self.socket = null;
          setTimeout(function() { self.setupPolls(); }, Math.random() * 15000);
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
        self.clarificationsOffset = 0;  // Return pagination to start on refresh
        self.refreshClarifications();
      }, 5 * 60 * 1000);

      self.rankingInterval =
          setInterval(self.refreshRanking.bind(self), 5 * 60 * 1000);
    }
  }

  initClock(start, finish, deadline) {
    let self = this;

    self.startTime = start;
    self.finishTime = finish;
    // Once the clock is ready, we can now connect to the socket.
    self.connectSocket();
    if (self.options.isPractice) {
      self.elements.clock.html('&infin;');
      return;
    }
    if (deadline) self.submissionDeadline = deadline;
    if (!self.clockInterval) {
      self.updateClock();
      self.clockInterval = setInterval(self.updateClock.bind(self), 1000);
    }
  }

  contestLoaded(contest) {
    let self = this;
    if (contest.status == 'error') {
      if (!OmegaUp.loggedIn) {
        window.location = '/login/?redirect=' + escape(window.location);
      } else if (contest.start_time) {
        let f = (function(x, y) {
          return function() {
            let t = new Date();
            self.elements.loadingOverlay.html(
                x + ' ' + FormatDelta(y.getTime() - t.getTime()));
            if (t.getTime() < y.getTime()) {
              setTimeout(f, 1000);
            } else {
              // TODO(pablo): Implement this for more than just contests.
              API.Contest.details({contest_alias: x})
                  .then(contestLoaded.bind(self))
                  .fail(UI.ignoreError);
            }
          }
        })(self.options.contestAlias, contest.start_time);
        setTimeout(f, 1000);
      } else {
        self.elements.loadingOverlay.html('404');
      }
      return;
    }
    if (self.options.isPractice && contest.finish_time &&
        Date.now() < contest.finish_time.getTime()) {
      window.location = window.location.pathname.replace(/\/practice.*/, '/');
      return;
    }

    if (contest.hasOwnProperty('problemset_id')) {
      self.options.problemsetId = contest.problemset_id;
    }

    $('#title .contest-title').html(UI.escape(contest.title));
    self.updateSummary(contest);
    self.submissionGap = parseInt(contest.submission_gap);
    if (!(self.submissionGap > 0)) self.submissionGap = 0;

    self.initClock(contest.start_time, contest.finish_time,
                   contest.submission_deadline);
    self.initProblems(contest);

    let problemSelect = $('select', self.elements.clarification);
    let problemTemplate = $('#problem-list .template');
    for (let idx in contest.problems) {
      let problem = contest.problems[idx];
      let problemName = problem.letter + '. ' + UI.escape(problem.title);

      let prob = problemTemplate.clone()
                     .removeClass('template')
                     .addClass('problem_' + problem.alias);
      $('.name', prob)
          .attr('href', '#problems/' + problem.alias)
          .html(problemName);
      self.elements.problemList.append(prob);

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
  }

  initProblems(contest) {
    let self = this;
    self.currentContest = contest;
    self.contestAdmin = contest.admin;
    let problems = contest.problems;
    for (let i = 0; i < problems.length; i++) {
      let problem = problems[i];
      let alias = problem.alias;
      if (typeof(problem.runs) === 'undefined') {
        problem.runs = [];
      }
      self.problems[alias] = problem;
    }
    self.elements.rankingTable.problems = problems;
    self.elements.rankingTable.showPenalty = contest.show_penalty;
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
      clock = '-' + FormatDelta(self.startTime.getTime() - now);
    } else if (now > countdownTime.getTime()) {
      // Contest for self user is over
      clock = '00:00:00';
      clearInterval(self.clockInterval);
      self.clockInterval = null;

      // Show go-to-practice-mode messages on contest end
      if (now > self.finishTime.getTime()) {
        if (self.options.contestAlias) {
          UI.warning('<a href="/arena/' + self.options.contestAlias +
                     '/practice/">' + T.arenaContestEndedUsePractice + '</a>');
          $('#new-run-practice-msg').show();
          $('#new-run-practice-msg a')
              .prop('href',
                    '/arena/' + self.options.contestAlias + '/practice/');
        }
        $('#new-run').hide();
      }
    } else {
      clock = FormatDelta(countdownTime.getTime() - now);
    }
    self.elements.clock.text(clock);
  }

  updateRunFallback(guid) {
    let self = this;
    if (self.socket != null) return;
    setTimeout(function() {
      API.Run.status({run_alias: guid})
          .then(self.updateRun.bind(self))
          .fail(UI.ignoreError);
    }, 5000);
  }

  updateRun(run) {
    let self = this;

    self.trackRun(run);

    if (self.socket != null) return;

    if (run.status == 'ready') {
      if (!self.options.isPractice && !self.options.isOnlyProblem &&
          self.options.contestAlias != 'admin') {
        self.refreshRanking();
      }
    } else {
      self.updateRunFallback(run.guid);
    }
  }

  refreshRanking() {
    let self = this;
    if (self.options.contestAlias) {
      API.Contest.scoreboard({contest_alias: self.options.contestAlias})
          .then(self.rankingChange.bind(self))
          .fail(UI.ignoreError);
    } else if (self.options.assignmentAlias) {
      API.Course.assignmentScoreboard({
                  course_alias: self.options.courseAlias,
                  assignment_alias: self.options.assignmentAlias
                })
          .then(self.rankingChange.bind(self))
          .fail(UI.ignoreError);
    }
  }

  rankingChange(data) {
    let self = this;
    self.onRankingChanged(data);

    let params = {
      contest_alias: self.options.contestAlias,
    };
    if (self.options.scoreboardToken) {
      params.token = self.options.scoreboardToken;
    }
    API.Contest.scoreboardEvents(params)
        .then(self.onRankingEvents.bind(self))
        .fail(UI.ignoreError);
  }

  onRankingChanged(data) {
    let self = this;
    $('tbody.inserted', self.elements.miniRanking).remove();

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

      let username = rank.username + ((rank.name == rank.username) ?
                                          '' :
                                          (' (' + UI.escape(rank.name) + ')'));

      currentRankingState[username] = {place: rank.place, accepted: {}};

      // Update problem scores.
      let totalRuns = 0;
      for (let alias in order) {
        if (!order.hasOwnProperty(alias)) continue;
        let problem = rank.problems[order[alias]];
        totalRuns += problem.runs;

        if (self.problems[alias]) {
          if (rank.username == OmegaUp.username) {
            $('#problems .problem_' + alias + ' .solved')
                .html('(' + problem.points + ' / ' +
                      self.problems[alias].points + ')');
            self.updateProblemScore(alias, self.problems[alias].points,
                                    problem.points);
          }
        }
      }

      // update miniranking
      if (i < 10) {
        let r = $('tbody.user-list-template', self.elements.miniRanking)
                    .clone()
                    .removeClass('user-list-template')
                    .addClass('inserted');

        $('.position', r).html(rank.place);
        $('.user', r)
            .html('<span title="' + username + '">' + rank.username +
                  UI.getFlag(rank['country']) + '</span>');
        $('.points', r).html(rank.total.points);
        $('.penalty', r).html(rank.total.penalty);

        self.elements.miniRanking.append(r);
      }
    }

    self.elements.rankingTable.ranking = ranking;
    if (data.time) {
      self.elements.rankingTable.lastUpdated = OmegaUp.remoteTime(data.time);
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
        curr.total.points
      ]);

      // check if to add to navigator
      if (curr.total.points > navigatorData[navigatorData.length - 1][1]) {
        navigatorData.push([
          this.startTime.getTime() + curr.delta * 60 * 1000,
          curr.total.points
        ]);
      }
    }

    // convert datas to series
    for (let i in dataInSeries) {
      if (dataInSeries.hasOwnProperty(i)) {
        dataInSeries[i].push([
          Math.min(this.finishTime.getTime(), Date.now()),
          dataInSeries[i][dataInSeries[i].length - 1][1]
        ]);
        series.push({
          name: i,
          rank: this.currentRanking[usernames[i]],
          data: dataInSeries[i],
          step: true
        });
      }
    }

    series.sort(function(a, b) { return a.rank - b.rank; });

    navigatorData.push([
      Math.min(this.finishTime.getTime(), Date.now()),
      navigatorData[navigatorData.length - 1][1]
    ]);
    this.createChart(series, navigatorData);
  }

  createChart(series, navigatorSeries) {
    let self = this;
    if (series.length == 0) return;

    Highcharts.setOptions({colors: ScoreboardColors});

    window.chart = new Highcharts.StockChart({
      chart: {renderTo: 'ranking-chart', height: 300, spacingTop: 20},

      xAxis: {
        ordinal: false,
        min: self.startTime.getTime(),
        max: Math.min(self.finishTime.getTime(), Date.now())
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
        })(self.problems)
      },

      plotOptions: {
        series: {
          animation: false,
          lineWidth: 3,
          states: {hover: {lineWidth: 3}},
          marker: {radius: 5, symbol: 'circle', lineWidth: 1}
        }
      },

      navigator: {
        series: {
          type: 'line',
          step: true,
          lineWidth: 3,
          lineColor: '#333',
          data: navigatorSeries
        }
      },

      rangeSelector: {enabled: false},

      series: series
    });
  }

  refreshClarifications() {
    let self = this;
    API.Contest.clarifications({
                 contest_alias: self.options.contestAlias,
                 offset: self.clarificationsOffset,
                 rowcount: self.clarificationsRowcount,
               })
        .then(self.clarificationsChange.bind(self))
        .fail(UI.ignoreError);
  }

  updateClarification(clarification) {
    let self = this;
    let r = null;
    let anchor =
        'clarifications/clarification-' + clarification.clarification_id;
    if (self.clarifications[clarification.clarification_id]) {
      r = self.clarifications[clarification.clarification_id];

      self.notifications.notify({
        id: 'clarification-' + clarification.clarification_id,
        author: clarification.author,
        contest: clarification.contest_alias,
        problem: clarification.problem_alias,
        message: clarification.message,
        answer: clarification.answer,
        anchor: '#' + anchor,
        modificationTime: clarification.time.getTime()
      });
    } else {
      r = $('.clarifications tbody.clarification-list tr.template')
              .clone()
              .removeClass('template')
              .addClass('inserted');

      if (self.contestAdmin) {
        (function(id, answerNode) {
          let responseFormNode =
              $('#create-response-form', answerNode).removeClass('template');
          let cannedResponse = $('#create-response-canned', answerNode);
          cannedResponse.on('change', function() {
            if (cannedResponse.val() === 'other') {
              $('#create-response-text', answerNode).show();
            } else {
              $('#create-response-text', answerNode).hide();
            }
          });
          if (clarification.public == 1) {
            $('#create-response-is-public', responseFormNode)
                .attr('checked', 'checked');
          }
          responseFormNode.on('submit', function() {
            let responseText = null;
            if ($('#create-response-canned', answerNode).val() === 'other') {
              responseText = $('#create-response-text', this).val();
            } else {
              responseText =
                  $('#create-response-canned>option:selected', this).html();
            }
            API.Clarification
                .update({
                  clarification_id: id,
                  answer: responseText,
                  'public':
                      $('#create-response-is-public', this)[0].checked ? 1 : 0
                })
                .then(function() {
                  $('pre', answerNode).html(responseText);
                  $('#create-response-text', answerNode).val('');
                })
                .fail(function() {
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
    if (self.contestAdmin) $('.author', r).html(clarification.author);
    $('.time', r)
        .html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S',
                                    clarification.time.getTime()));
    $('.message', r).html(UI.escape(clarification.message));
    $('.answer pre', r).html(UI.escape(clarification.answer));
    if (clarification.answer) {
      self.answeredClarifications++;
    }

    if (self.contestAdmin != !!clarification.answer) {
      self.notifications.notify({
        id: 'clarification-' + clarification.clarification_id,
        author: clarification.author,
        contest: clarification.contest_alias,
        problem: clarification.problem_alias,
        message: clarification.message,
        answer: clarification.answer,
        anchor: '#' + anchor,
        modificationTime: clarification.time.getTime()
      });
    }

    if (!self.clarifications[clarification.clarification_id]) {
      $('.clarifications tbody.clarification-list').prepend(r);
      self.clarifications[clarification.clarification_id] = r;
    }
  }

  clarificationsChange(data) {
    let self = this;
    if (data.status != 'ok') {
      return;
    }
    $('.clarifications tr.inserted').remove();
    if (data.clarifications.length > 0 &&
        data.clarifications.length < self.clarificationsRowcount) {
      $('#clarifications-count').html('(' + data.clarifications.length + ')');
    } else if (data.clarifications.length >= self.clarificationsRowcount) {
      $('#clarifications-count').html('(' + data.clarifications.length + '+)');
    }

    let previouslyAnswered = self.answeredClarifications;
    self.answeredClarifications = 0;
    self.clarifications = {};

    for (let i = data.clarifications.length - 1; i >= 0; i--) {
      self.updateClarification(data.clarifications[i]);
    }

    if (self.answeredClarifications > previouslyAnswered &&
        self.activeTab != 'clarifications') {
      $('#clarifications-count').css('font-weight', 'bold');
    }
  }

  updateAllowedLanguages(lang_array) {
    let self = this;

    let can_submit = lang_array.length != 0;

    $('.runs').toggle(can_submit);
    $('.data').toggle(can_submit);
    $('.best-solvers').toggle(can_submit);
    $('option', self.elements.submitForm.language)
        .each(function(index, item) {
          item = $(item);
          item.toggle(lang_array.indexOf(item.val()) >= 0);
        });
  }

  selectDefaultLanguage() {
    let self = this;
    let langElement = self.elements.submitForm.language;
    if (self.preferredLanguage) {
      $('option', langElement)
          .each(function() {
            let option = $(this);
            if (option.css('display') == 'none') return;
            if (option.val() != self.preferredLanguage) return;
            option.prop('selected', true);
            return false;
          });
    }
    if (langElement.val()) return;

    $('option', langElement)
        .each(function() {
          let option = $(this);
          if (option.css('display') != 'none') {
            option.prop('selected', true);
            langElement.trigger('change');
            return false;
          }
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
        }
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
            input: (value) => { this.code = value; },
            change: (value) => { this.code = value; },
          }
        });
      },
      components: {
        'omegaup-arena-code-view': arena_CodeView,
      }
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
    if (self.previousHash == window.location.hash + '/new-run' ||
        window.location.hash == self.previousHash + '/new-run') {
      problemChanged = false;
    }
    self.previousHash = window.location.hash;

    if (problem && self.problems[problem[1]]) {
      let newRun = problem[2];
      self.currentProblem = problem = self.problems[problem[1]];

      $('.active', self.elements.problemList).removeClass('active');
      $('.problem_' + problem.alias, self.elements.problemList)
          .addClass('active');

      function update(problem) {
        // TODO: Make #problem a component
        $('#summary').hide();
        $('#problem').show();
        $('#problem > .title')
            .html(problem.letter + '. ' + UI.escape(problem.title));
        $('#problem .data .points').html(problem.points);
        $('#problem .memory_limit').html(problem.memory_limit / 1024 + 'MB');
        $('#problem .time_limit').html(problem.time_limit / 1000 + 's');
        $('#problem .overall_wall_time_limit')
            .html(problem.overall_wall_time_limit / 1000 + 's');
        $('#problem .statement').html(problem.problem_statement);
        self.myRuns.attach($('#problem .runs'));
        let karel_langs = ['kp', 'kj'];
        let language_array = problem.languages;
        if (karel_langs.every(function(x) {
              return language_array.indexOf(x) != -1;
            })) {
          let original_href = $('#problem .karel-js-link a').attr('href');
          let hash_index = original_href.indexOf('#');
          if (hash_index != -1) {
            original_href = original_href.substring(0, hash_index);
          }
          if (problem.sample_input) {
            $('#problem .karel-js-link a')
                .attr('href', original_href + '#mundo:' +
                                  encodeURIComponent(problem.sample_input));
          } else {
            $('#problem .karel-js-link a').attr('href', original_href);
          }
          $('#problem .karel-js-link').removeClass('hide');
        } else {
          $('#problem .karel-js-link').addClass('hide');
        }
        if (problem.source) {
          $('#problem .source span').html(UI.escape(problem.source));
          $('#problem .source').show();
        } else {
          $('#problem .source').hide();
        }
        if (problem.problemsetter) {
          $('#problem .problemsetter a')
              .html(UI.escape(problem.problemsetter.name))
              .attr('href', '/profile/' + problem.problemsetter.username + '/');
          $('#problem .problemsetter').show();
        } else {
          $('#problem .problemsetter').hide();
        }
        $('#problem .runs tfoot td a')
            .attr('href', '#problems/' + problem.alias + '/new-run');
        self.installLibinteractiveHooks();

        $('#problem tbody.added').remove();

        self.updateAllowedLanguages(language_array);
        self.selectDefaultLanguage();

        function updateRuns(runs) {
          if (runs) {
            for (let run of runs) {
              self.trackRun(run);
            }
          }
          self.myRuns.filter_problem(problem.alias);
        }

        if (self.options.isPractice || self.options.isOnlyProblem) {
          API.Problem.runs({problem_alias: problem.alias})
              .then(function(data) { updateRuns(data.runs); })
              .fail(UI.apiError);
        } else {
          updateRuns(problem.runs);
        }

        self.mountEditor(problem);
        MathJax.Hub.Queue(
            ['Typeset', MathJax.Hub, $('#problem .statement').get(0)]);
        self.initSubmissionCountdown();
      }

      if (problemChanged) {
        if (problem.problem_statement) {
          update(problem);
        } else {
          let problemset = self.computeProblemsetArg();
          API.Problem.details(
                         $.extend(problemset, {problem_alias: problem.alias}))
              .then(function(problem_ext) {
                problem.source = problem_ext.source;
                problem.problemsetter = problem_ext.problemsetter;
                problem.problem_statement = problem_ext.problem_statement;
                problem.sample_input = problem_ext.sample_input;
                problem.runs = problem_ext.runs;
                problem.templates = problem_ext.templates;
                self.preferredLanguage = problem_ext.preferred_language;
                update(problem);
              })
              .fail(UI.apiError);
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
      }
    } else if (self.activeTab == 'problems') {
      $('#problem').hide();
      $('#summary').show();
      $('.active', self.elements.problemList).removeClass('active');
      $('.summary', self.elements.problemList).addClass('active');
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

  detectShowRun() {
    let self = this;
    let showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    let showRunMatch = window.location.hash.match(showRunRegex);
    if (showRunMatch) {
      $('#overlay form').hide();
      $('#overlay').show();
      API.Run.details({run_alias: showRunMatch[1]})
          .then(function(data) {
            self.displayRunDetails(showRunMatch[1], data);
          })
          .fail(UI.apiError);
    }
  }

  hideOverlay() {
    $('#overlay').hide();
    window.location.hash = window.location.hash.substring(
        0, window.location.hash.lastIndexOf('/'));
  }

  bindGlobalHandlers() {
    let self = this;
    $('#overlay, .close').on('click', self.onCloseSubmit.bind(self));
    self.elements.submitForm.language.on('change',
                                         self.onLanguageSelect.bind(self));
    self.elements.submitForm.on('submit', self.onSubmit.bind(self));
  }

  onCloseSubmit(e) {
    let self = this;
    if (e.target.id === 'overlay' || e.target.className === 'close') {
      $('#clarification', self.elements.submitForm).hide();
      self.hideOverlay();
      self.clearInputFile();
      return false;
    }
  }

  clearInputFile() {
    let self = this;
    // This worked, nay, was required, on older browsers.
    // It stopped working sometime in 2017, and now .val(null)
    // is enough to clear the input field.
    // Leaving this here for now in case some older browsers
    // still require it.
    self.elements.submitForm.file.replaceWith(
        self.elements.submitForm.file =
            self.elements.submitForm.file.clone(true));
    self.elements.submitForm.file.val(null);
  }

  initSubmissionCountdown() {
    let self = this;
    let nextSubmissionTimestamp = new Date(0);
    $('#submit input[type=submit]').removeAttr('value').removeAttr('disabled');
    let problem = self.problems[self.currentProblem.alias];
    if (typeof(problem) !== 'undefined') {
      if (typeof(problem.nextSubmissionTimestamp) !== 'undefined') {
        nextSubmissionTimestamp =
            new Date(problem.nextSubmissionTimestamp * 1000);
      } else if (typeof(problem.runs) !== 'undefined' &&
                 problem.runs.length > 0) {
        nextSubmissionTimestamp =
            new Date(problem.runs[problem.runs.length - 1].time.getTime() +
                     self.currentContest.submissions_gap * 1000);
      }
    }
    if (self.submissionGapInterval) {
      clearInterval(self.submissionGapInterval);
      self.submissionGapInterval = 0;
    }
    self.submissionGapInterval = setInterval(function() {
      let submissionGapSecondsRemaining =
          Math.ceil((nextSubmissionTimestamp - Date.now()) / 1000);
      if (submissionGapSecondsRemaining > 0) {
        $('#submit input[type=submit]')
            .attr('disabled', 'disabled')
            .val(UI.formatString(
                T.arenaRunSubmitWaitBetweenUploads,
                {submissionGap: submissionGapSecondsRemaining}));
      } else {
        $('#submit input[type=submit]')
            .removeAttr('value')
            .removeAttr('disabled');
        clearInterval(self.submissionGapInterval);
      }
    }, 1000);
  }

  onLanguageSelect(e) {
    let self = this;
    let lang = $(e.target).val();
    let ext = $('.submit-filename-extension', self.elements.submitForm);
    if (lang == 'cpp11') {
      ext.text('.cpp');
    } else if (lang && lang != 'cat') {
      ext.text('.' + lang);
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

    if (!self.options.isOnlyProblem &&
        (self.problems[self.currentProblem.alias].last_submission +
             self.submissionGap * 1000 >
         Date.now())) {
      alert(UI.formatString(T.arenaRunSubmitWaitBetweenUploads,
                            {submissionGap: self.submissionGap}));
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

      reader.onload = function(e) { self.submitRun(e.target.result); };

      let extension = file.name.split(/\./);
      extension = extension[extension.length - 1];

      if (langSelect.val() != 'cat' || file.type.indexOf('text/') === 0 ||
          extension == 'cpp' || extension == 'c' || extension == 'cs' ||
          extension == 'java' || extension == 'txt' || extension == 'hs' ||
          extension == 'kp' || extension == 'kj' || extension == 'p' ||
          extension == 'pas' || extension == 'py' || extension == 'rb' ||
          extension == 'lua') {
        if (file.size >= 10 * 1024) {
          alert(UI.formatString(T.arenaRunSubmitFilesize, {limit: '10kB'}));
          return false;
        }
        reader.readAsText(file, 'UTF-8');
      } else {
        // 100kB _must_ be enough for anybody.
        if (file.size >= 100 * 1024) {
          alert(UI.formatString(T.arenaRunSubmitFilesize, {limit: '100kB'}));
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
      return {contest_alias: self.options.contestAlias};
    }
    return {problemset_id: self.options.problemsetId};
  }

  submitRun(code) {
    let self = this;
    let problemset = self.options.isPractice ? {} : self.computeProblemsetArg();
    let lang = self.elements.submitForm.language.val();

    $('input', self.elements.submitForm).attr('disabled', 'disabled');
    API.Run.create($.extend(problemset,
                            {
                              problem_alias: self.currentProblem.alias,
                              language: lang,
                              source: code,
                            }))
        .then(function(run) {
          if (self.options.isLockdownMode && sessionStorage) {
            sessionStorage.setItem('run:' + run.guid, code);
          }

          if (!self.options.isOnlyProblem) {
            self.problems[self.currentProblem.alias].last_submission =
                Date.now();
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

          $('input', self.elements.submitForm).removeAttr('disabled');
          self.hideOverlay();
          self.clearInputFile();
          self.initSubmissionCountdown();
        })
        .fail(function(run) {
          alert(run.error);
          $('input', self.elements.submitForm).removeAttr('disabled');
        }

              );
  }

  updateSummary(contest) {
    let self = this;
    if (!self.summaryView.attached) {
      let summary = $('#summary');
      ko.applyBindings(self.summaryView, summary[0]);
      self.summaryView.attached = true;
    }
    self.summaryView.title(contest.title);
    self.summaryView.description(contest.description);
    let duration = contest.finish_time.getTime() - contest.start_time.getTime();
    self.summaryView.windowLength(
        FormatDelta((contest.window_length * 60000) || duration));
    self.summaryView.contestOrganizer(contest.director);
    self.summaryView.startTime(Highcharts.dateFormat(
        '%Y-%m-%d %H:%M:%S', contest.start_time.getTime()));
    self.summaryView.finishTime(Highcharts.dateFormat(
        '%Y-%m-%d %H:%M:%S', contest.finish_time.getTime()));
    self.summaryView.scoreboardCutoff(Highcharts.dateFormat(
        '%Y-%m-%d %H:%M:%S',
        contest.start_time.getTime() + duration * contest.scoreboard / 100));
  }

  displayRunDetails(guid, data) {
    let self = this;
    let problemAdmin = data.admin;

    if (data.status == 'error') {
      self.hideOverlay();
      return;
    }

    if (data.compile_error) {
      $('#run-details .compile_error pre').html(UI.escape(data.compile_error));
      $('#run-details .compile_error').show();
    } else {
      $('#run-details .compile_error').hide();
      $('#run-details .compile_error pre').html('');
    }
    if (data.logs) {
      $('#run-details .logs pre').html(UI.escape(data.logs));
      $('#run-details .logs').show();
    } else {
      $('#run-details .logs').hide();
      $('#run-details .logs pre').html('');
    }
    if (data.source.indexOf('data:') === 0) {
      $('#run-details .source')
          .html('<a href="' + data.source + '" download="data.zip">' +
                T.wordsDownload + '</a>');
    } else if (data.source == 'lockdownDetailsDisabled') {
      $('#run-details .source')
          .html(UI.escape((typeof(sessionStorage) !== 'undefined' &&
                           sessionStorage.getItem('run:' + guid)) ||
                          T.lockdownDetailsDisabled));
    } else {
      $('#run-details .source').html(UI.escape(data.source));
    }

    if (data.judged_by) {
      $('#run-details .judged_by pre').html(UI.escape(data.judged_by));
      $('#run-details .judged_by').show();
    } else {
      $('#run-details .judged_by').hide();
      $('#run-details .judged_by pre').html('');
    }

    $('#run-details .cases div').remove();
    $('#run-details .cases table').remove();
    $('#run-details .download a.sourcecode')
        .attr('href', window.URL.createObjectURL(
                          new Blob([data.source], {'type': 'text/plain'})))
        .attr('download', 'Main.' + data.language);
    if (problemAdmin) {
      $('#run-details .download a.output')
          .attr('href', '/api/run/download/run_alias/' + data.guid + '/');
      $('#run-details .download a.details')
          .attr('href',
                '/api/run/download/run_alias/' + data.guid + '/complete/true/');
      $('#run-details .download a').show();
    } else {
      $('#run-details .download a').hide();
      $('#run-details .download a.sourcecode').show();
    }

    function numericSort(key) {
      function isDigit(x) { return '0' <= x && x <= '9'; }

      return function(x, y) {
        let i = 0, j = 0;
        for (; i < x[key].length && j < y[key].length; i++, j++) {
          if (isDigit(x[key][i]) && isDigit(x[key][j])) {
            let nx = 0, ny = 0;
            while (i < x[key].length && isDigit(x[key][i]))
              nx = (nx * 10) + parseInt(x[key][i++]);
            while (j < y[key].length && isDigit(y[key][j]))
              ny = (ny * 10) + parseInt(y[key][j++]);
            i--;
            j--;
            if (nx != ny) return nx - ny;
          } else if (x[key][i] < y[key][j]) {
            return -1;
          } else if (x[key][i] > y[key][j]) {
            return 1;
          }
        }
        return (x[key].length - i) - (y[key].length - j);
      };
    }

    // TODO(lhchavez): Use only data.details once backendv1 is deprecated.
    let detailsGroups = data.groups;
    if (data.details) {
      detailsGroups = data.details.groups;
    }
    if (detailsGroups && detailsGroups.length) {
      detailsGroups.sort(numericSort('group'));
      for (let i = 0; i < detailsGroups.length; i++) {
        detailsGroups[i].cases.sort(numericSort('name'));
      }

      let groups =
          $('<table></table>')
              .append($('<thead></thead>')
                          .append($('<tr></tr>')
                                      .append('<th>' + T.wordsGroup + '</th>')
                                      .append('<th>' + T.wordsCase + '</th>')
                                      .append('<th>' + T.wordsVerdict + '</th>')
                                      .append('<th colspan="3">' + T.rankScore +
                                              '</th>')
                                      .append('<th width="1"></th>')));

      for (let i = 0; i < detailsGroups.length; i++) {
        let g = detailsGroups[i];
        let cases = $('<tbody></tbody>').hide();
        groups.append(
            $('<tbody></tbody>')
                .append(
                    $('<tr class="group"></tr>')
                        .append('<th class="center">' + UI.escape(g.group) +
                                '</th>')
                        .append('<th colspan="2"></th>')
                        .append('<th class="score">' +
                                (g.contest_score !== undefined ?
                                     g.contest_score :
                                     g.score) +
                                '</th>')
                        .append('<th class="center" width="10">' +
                                (g.max_score !== undefined ? '/' : '') +
                                '</th>')
                        .append('<th>' +
                                (g.max_score !== undefined ? g.max_score : '') +
                                '</th>')
                        .append(
                            $('<td></td>')
                                .append(
                                    $('<span class="collapse glyphicon ' +
                                      'glyphicon-collapse-down"></span>')
                                        .on('click', (function(cases) {
                                              return function(ev) {
                                                let target = $(ev.target);
                                                if (target.hasClass(
                                                        'glyphicon-collapse-down')) {
                                                  target.removeClass(
                                                      'glyphicon-collapse-down');
                                                  target.addClass(
                                                      'glyphicon-collapse-up');
                                                } else {
                                                  target.addClass(
                                                      'glyphicon-collapse-down');
                                                  target.removeClass(
                                                      'glyphicon-collapse-up');
                                                }
                                                cases.toggle();
                                                return false;
                                              };
                                            })(cases))))));
        for (let j = 0; j < g.cases.length; j++) {
          let c = g.cases[j];
          let caseRow =
              $('<tr></tr>')
                  .append('<td></td>')
                  .append('<td class="center">' + c.name + '</td>')
                  .append('<td class="center">' + T['verdict' + c.verdict] +
                          '</td>')
                  .append('<td class="score">' +
                          (c.contest_score !== undefined ? c.contest_score :
                                                           c.score) +
                          '</td>')
                  .append('<td class="center" width="10">' +
                          (c.max_score !== undefined ? '/' : '') + '</td>')
                  .append('<td>' +
                          (c.max_score !== undefined ? c.max_score : '') +
                          '</td>');
          cases.append(caseRow);
          if (problemAdmin && c.meta) {
            let metaRow =
                $('<tr class="meta"></tr>')
                    .append('<td colspan="6"><pre>' +
                            JSON.stringify(c.meta, null, 2) + '</pre></td>')
                    .hide();
            caseRow.append(
                $('<td></td>')
                    .append(
                        $('<span class="collapse glyphicon glyphicon-list-alt">' +
                          '</span>')
                            .on('click', (function(metaRow) {
                                  return function(ev) {
                                    metaRow.toggle();
                                    return false;
                                  };
                                })(metaRow))));
            cases.append(metaRow);
          }
        }
        groups.append(cases);
      }
      $('#run-details .cases').append(groups);
      $('#run-details .cases').show();
    } else {
      $('#run-details .cases').hide();
    }
    $('#overlay form').hide();
    $('#overlay').show();
    $('#run-details').show();
  }

  trackRun(run) {
    let self = this;
    self.runs.trackRun(run);
    if (run.username == OmegaUp.username) {
      self.myRuns.trackRun(run);
      if (typeof self.problems[run.alias] != 'undefined') {
        self.updateProblemScore(run.alias, self.problems[run.alias].points, 0);
      }
    }
  }

  updateProblemScore(alias, maxScore, previousScore) {
    let self = this;
    $('.problem_' + alias + ' .solved')
        .html('(' + self.myRuns.getMaxScore(alias, previousScore) + ' / ' +
              maxScore + ')');
  }
}
;

class RunView {
  constructor(arena) {
    let self = this;
    self.arena = arena;
    self.row_count = 100;
    self.filter_verdict = ko.observable();
    self.filter_status = ko.observable();
    self.filter_language = ko.observable();
    self.filter_problem = ko.observable();
    self.filter_username = ko.observable();
    self.filter_offset = ko.observable(0);
    self.runs = ko.observableArray().extend({deferred: true});
    self.filtered_runs =
        ko.pureComputed(function() {
            let cached_verdict = self.filter_verdict();
            let cached_status = self.filter_status();
            let cached_language = self.filter_language();
            let cached_problem = self.filter_problem();
            let cached_username = self.filter_username();
            if (!cached_verdict && !cached_status && !cached_language &&
                !cached_problem && !cached_username) {
              return self.runs();
            }
            return self.runs().filter(function(val) {
              if (cached_verdict && cached_verdict != val.verdict()) {
                return false;
              }
              if (cached_status && cached_status != val.status()) {
                return false;
              }
              if (cached_language && cached_language != val.language()) {
                return false;
              }
              if (cached_problem && cached_problem != val.alias()) {
                return false;
              }
              if (cached_username && cached_username != val.username()) {
                return false;
              }
              return true;
            });
          }, self).extend({deferred: true});
    self.sorted_runs =
        ko.pureComputed(function() {
            return self.filtered_runs().sort(function(a, b) {
              if (a.time().getTime() == b.time().getTime()) {
                return a.guid == b.guid ? 0 : (a.guid < b.guid ? -1 : 1);
              }
              // Newest runs appear on top.
              return b.time().getTime() - a.time().getTime();
            });
          }, self).extend({deferred: true});
    self.display_runs =
        ko.pureComputed(function() {
            let offset = self.filter_offset();
            return self.sorted_runs().slice(offset, offset + self.row_count);
          }, self).extend({deferred: true});
    self.observableRunsIndex = {};
    self.attached = false;
  }

  getMaxScore(alias, previousScore) {
    let self = this;
    let runs = self.runs();
    let maxScore = previousScore;
    for (let run of runs) {
      if (alias != run.alias()) {
        continue;
      }
      let score = run.contest_score();
      if (score > maxScore) {
        maxScore = score;
      }
    }
    return maxScore;
  }

  attach(elm) {
    let self = this;

    if (self.attached) return;

    $('.runspager .runspagerprev', elm)
        .on('click', function() {
          if (self.filter_offset() < self.row_count) {
            self.filter_offset(0);
          } else {
            self.filter_offset(self.filter_offset() - self.row_count);
          }
        });

    $('.runspager .runspagernext', elm)
        .on('click', function() {
          self.filter_offset(self.filter_offset() + self.row_count);
        });

    UI.userTypeahead($('.runsusername', elm), function(event, item) {
      self.filter_username(item.value);
    });

    $('.runsusername-clear', elm)
        .on('click', function() {
          $('.runsusername', elm).val('');
          self.filter_username('');
        });

    UI.problemTypeahead($('.runsproblem', elm), function(event, item) {
      self.filter_problem(item.alias);
    });

    $('.runsproblem-clear', elm)
        .on('click', function() {
          $('.runsproblem', elm).val('');
          self.filter_problem('');
        });

    if (elm[0] && !ko.dataFor(elm[0])) ko.applyBindings(self, elm[0]);
    self.attached = true;
  }

  trackRun(run) {
    let self = this;
    if (!self.observableRunsIndex[run.guid]) {
      self.observableRunsIndex[run.guid] = new ObservableRun(self.arena, run);
      self.runs.push(self.observableRunsIndex[run.guid]);
    } else {
      self.observableRunsIndex[run.guid].update(run);
    }
  }

  clear(run) {
    let self = this;

    self.runs.removeAll();
    self.observableRunsIndex = {};
  }
}
;

class ObservableRun {
  constructor(arena, run) {
    let self = this;

    self.arena = arena;
    self.guid = run.guid;
    self.short_guid = run.guid.substring(0, 8);

    self.alias = ko.observable(run.alias);
    self.contest_alias = ko.observable(run.contest_alias);
    self.contest_score = ko.observable(run.contest_score);
    self.country_id = ko.observable(run.country_id);
    self.judged_by = ko.observable(run.judged_by);
    self.language = ko.observable(run.language);
    self.memory = ko.observable(run.memory);
    self.penalty = ko.observable(run.penalty);
    self.run_id = ko.observable(run.run_id);
    self.runtime = ko.observable(run.runtime);
    self.score = ko.observable(run.score);
    self.status = ko.observable(run.status);
    self.submit_delay = ko.observable(run.submit_delay);
    self.time = ko.observable(run.time);
    self.username = ko.observable(run.username);
    self.verdict = ko.observable(run.verdict);

    self.user_html = ko.pureComputed(self.$user_html, self);
    self.problem_url = ko.pureComputed(self.$problem_url, self);
    self.time_text = ko.pureComputed(self.$time_text, self);
    self.runtime_text = ko.pureComputed(self.$runtime_text, self);
    self.memory_text = ko.pureComputed(self.$memory_text, self);
    self.status_text = ko.pureComputed(self.$status_text, self);
    self.status_help = ko.pureComputed(self.$status_help, self);
    self.status_color = ko.pureComputed(self.$status_color, self);
    self.penalty_text = ko.pureComputed(self.$penalty_text, self);
    self.points = ko.pureComputed(self.$points, self);
    self.percentage = ko.pureComputed(self.$percentage, self);
    self.contest_alias_url = ko.pureComputed(self.$contest_alias_url, self);
  }

  update(run) {
    let self = this;
    for (let p in run) {
      if (!run.hasOwnProperty(p) || !self.hasOwnProperty(p) ||
          !(self[p] instanceof Function)) {
        continue;
      }
      if (self[p]() != run[p]) {
        self[p](run[p]);
      }
    }
  }

  showVerdictHelp(elm, ev) {
    let self = this;
    $(ev.target).popover('show');
  }

  $problem_url() {
    let self = this;
    return '/arena/problem/' + self.alias() + '/';
  }

  $contest_alias_url() {
    let self = this;
    return (self.contest_alias() === null) ?
               '' :
               '/arena/' + self.contest_alias() + '/';
  }

  $user_html() {
    let self = this;
    return UI.getProfileLink(self.username()) + UI.getFlag(self.country_id());
  }

  $time_text() {
    let self = this;
    return Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', self.time().getTime());
  }

  $runtime_text() {
    let self = this;
    if (self.status() == 'ready' && self.verdict() != 'JE' &&
        self.verdict() != 'CE') {
      let prefix = '';
      if (self.verdict() == 'TLE') {
        prefix = '>';
      }
      return prefix + (parseFloat(self.runtime() || '0') / 1000).toFixed(2) +
             ' s';
    } else {
      return '—';
    }
  }

  $memory_text() {
    let self = this;
    if (self.status() == 'ready' && self.verdict() != 'JE' &&
        self.verdict() != 'CE') {
      let prefix = '';
      if (self.verdict() == 'MLE') {
        prefix = '>';
      }
      return prefix + (parseFloat(self.memory()) / (1024 * 1024)).toFixed(2) +
             ' MB';
    } else {
      return '—';
    }
  }

  $penalty_text() {
    let self = this;

    if (self.status() == 'ready' && self.verdict() != 'JE' &&
        self.verdict() != 'CE') {
      return self.penalty();
    } else {
      return '—';
    }
  }

  $status_text() {
    let self = this;

    return self.status() == 'ready' ? T['verdict' + self.verdict()] :
                                      self.status();
  }

  $status_help() {
    let self = this;

    if (self.status() != 'ready' || self.verdict() == 'AC') {
      return null;
    }

    if (self.language() == 'kj' || self.language() == 'kp') {
      if (self.verdict() == 'RTE' || self.verdict() == 'RE') {
        return T.verdictHelpKarelRTE;
      } else if (self.verdict() == 'TLE' || self.verdict() == 'TO') {
        return T.verdictHelpKarelTLE;
      }
    }

    return T['verdictHelp' + self.verdict()];
  }

  $status_color() {
    let self = this;

    if (self.status() != 'ready') return '';

    if (self.verdict() == 'AC') {
      return '#CF6';
    } else if (self.verdict() == 'CE') {
      return '#F90';
    } else if (self.verdict() == 'JE') {
      return '#F00';
    } else {
      return '';
    }
  }

  $points() {
    let self = this;
    if (self.contest_score() != null && self.status() == 'ready' &&
        self.verdict() != 'JE' && self.verdict() != 'CE') {
      return parseFloat(self.contest_score() || '0').toFixed(2);
    } else {
      return '—';
    }
  }

  $percentage() {
    let self = this;
    if (self.status() == 'ready' && self.verdict() != 'JE' &&
        self.verdict() != 'CE') {
      return (parseFloat(self.score() || '0') * 100).toFixed(2) + '%';
    } else {
      return '—';
    }
  }

  details() {
    let self = this;
    window.location.hash += '/show-run:' + self.guid;
  }

  rejudge() {
    let self = this;
    API.Run.rejudge({run_alias: self.guid, debug: false})
        .then(function(data) {
          self.status('rejudging');
          self.arena.updateRunFallback(self.guid);
        })
        .fail(UI.ignoreError);
  }

  debug_rejudge() {
    let self = this;
    API.Run.rejudge({run_alias: self.guid, debug: true})
        .then(function(data) {
          self.status('rejudging');
          self.arena.updateRunFallback(self.guid);
        })
        .fail(UI.ignoreError);
  }
}
;
