var omegaup = typeof global === 'undefined' ?
                  (window.omegaup = window.omegaup || {}) :
                  (global.omegaup = global.omegaup || {});

omegaup.arena = omegaup.arena || {};

omegaup.arena.FormatDelta = function(delta) {
  var days = Math.floor(delta / (24 * 60 * 60 * 1000));
  delta -= days * (24 * 60 * 60 * 1000);
  var hours = Math.floor(delta / (60 * 60 * 1000));
  delta -= hours * (60 * 60 * 1000);
  var minutes = Math.floor(delta / (60 * 1000));
  delta -= minutes * (60 * 1000);
  var seconds = Math.floor(delta / 1000);

  var clock = '';

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
};

omegaup.arena.ScoreboardColors = [
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

omegaup.arena.GetOptionsFromLocation = function(arenaLocation) {
  var options = {
    isLockdownMode: false,
    isPractice: false,
    isOnlyProblem: false,
    disableClarifications: false,
    disableSockets: false,
    contestAlias: null,
    scoreboardToken: null
  };

  if ($('body').hasClass('lockdown')) {
    options.isLockdownMode = true;
  }

  if (arenaLocation.pathname.indexOf('/practice') !== -1) {
    options.isPractice = true;
  }

  if (arenaLocation.pathname.indexOf('/arena/problem/') !== -1) {
    options.isOnlyProblem = true;
    options.onlyProblemAlias =
        /\/arena\/problem\/([^\/]+)\/?/.exec(arenaLocation.pathname)[1];
  } else {
    options.contestAlias =
        /\/arena\/([^\/]+)\/?/.exec(arenaLocation.pathname)[1];
  }

  if (arenaLocation.search.indexOf('ws=off') !== -1) {
    options.disableSockets = true;
  }

  return options;
};

omegaup.arena.Arena = function(options) {
  var self = this;

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
  self.runs = new omegaup.arena.RunView(self);
  self.myRuns = new omegaup.arena.RunView(self);
  self.myRuns.filter_username(omegaup.OmegaUp.username);

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

  // Every time a recent event is shown, have self interval clear it after 30s.
  self.removeRecentEventClassTimeout = null;

  // The last known scoreboard event stream.
  self.currentEvents = null;

  // Currently opened notifications.
  self.currentNotifications = {count: 0, timer: null};

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

  // Setup any global hooks.
  self.installLibinteractiveHooks();

  var options = {
    attribute: 'data-bind'  // default "data-sbind"
  };
  ko.bindingProvider.instance = new ko.secureBindingsProvider(options);
  self.bindGlobalHandlers();
};

omegaup.arena.Arena.prototype.installLibinteractiveHooks = function() {
  var self = this;
  $('#libinteractive-download')
      .submit(function(e) {
        var form = $(e.target);
        var alias = e.target.attributes['data-alias'].value;
        var os = form.find('.download-os').val();
        var lang = form.find('.download-lang').val();
        var extension = (os == 'unix' ? '.tar.bz2' : '.zip');

        omegaup.UI.navigateTo(window.location.protocol + '//' +
                              window.location.host + '/templates/' + alias +
                              '/' + alias + '_' + os + '_' + lang + extension);

        return false;
      });

  $('#libinteractive-download .download-lang')
      .change(function(e) {
        var form = $('#libinteractive-download');
        form.find('.libinteractive-extension')
            .html(form.find('.download-lang').val());
      });
};

omegaup.arena.Arena.prototype.connectSocket = function() {
  var self = this;
  if (self.options.isPractice || self.options.disableSockets ||
      self.options.contestAlias == 'admin') {
    return false;
  }

  var uri;
  if (window.location.protocol === 'https:') {
    uri = 'wss:';
  } else {
    uri = 'ws:';
  }
  uri += '//' + window.location.host + '/api/contest/events/' +
         self.options.contestAlias + '/';
  if (self.options.scoreboardToken) {
    uri += '?token=' + self.options.scoreboardToken;
  }

  try {
    self.socket = new WebSocket(uri, 'com.omegaup.events');
    $('#title .socket-status').html('&bull;');
    self.socket.onmessage = function(message) {
      console.log(message);
      var data = JSON.parse(message.data);

      if (data.message == '/run/update/') {
        data.run.time = omegaup.OmegaUp.time(data.run.time * 1000);
        self.updateRun(data.run);
      } else if (data.message == '/clarification/update/') {
        if (self.options.disableClarifications) {
          data.clarification.time =
              omegaup.OmegaUp.time(data.clarification.time * 1000);
          self.updateClarification(data.clarification);
        }
      } else if (data.message == '/scoreboard/update/') {
        self.rankingChange(data.scoreboard);
      }
    };
    self.socket.onopen = function() {
      $('#title .socket-status').html('&bull;').css('color', '#080');
      self.socket_keepalive =
          setInterval((function(socket) {
                        return function() { socket.send('"ping"'); };
                      })(self.socket),
                      30000);
    };
    self.socket.onclose = function(e) {
      $('#title .socket-status').html('&cross;').css('color', '#800');
      self.socket = null;
      clearInterval(self.socket_keepalive);
      setTimeout(function() { self.setupPolls(); }, Math.random() * 15000);
      console.error(e);
    };
    self.socket.onerror = function(e) {
      $('#title .socket-status').html('&cross;').css('color', '#800');
      self.socket = null;
      clearInterval(self.socket_keepalive);
      setTimeout(function() { self.setupPolls(); }, Math.random() * 15000);
      console.error(e);
    };
  } catch (e) {
    self.socket = null;
    console.error(e);
  }
};

omegaup.arena.Arena.prototype.setupPolls = function() {
  var self = this;

  omegaup.API.getRanking(self.options.contestAlias,
                         self.rankingChange.bind(self));
  omegaup.API.getClarifications(
      self.options.contestAlias, self.clarificationsOffset,
      self.clarificationsRowcount, self.clarificationsChange.bind(self));

  if (!self.socket) {
    self.clarificationInterval = setInterval(function() {
      self.clarificationsOffset = 0;  // Return pagination to start on refresh
      omegaup.API.getClarifications(
          self.options.contestAlias, self.clarificationsOffset,
          self.clarificationsRowcount, self.clarificationsChange.bind(self));
    }, 5 * 60 * 1000);

    self.rankingInterval = setInterval(function() {
      omegaup.API.getRanking(self.options.contestAlias,
                             self.rankingChange.bind(self));
    }, 5 * 60 * 1000);
  }
};

omegaup.arena.Arena.prototype.initClock = function(start, finish, deadline) {
  var self = this;

  self.startTime = start;
  self.finishTime = finish;
  if (self.options.isPractice) {
    $('#title .clock').html('&infin;');
    return;
  }
  if (deadline) self.submissionDeadline = deadline;
  if (!self.clockInterval) {
    self.updateClock();
    self.clockInterval = setInterval(self.updateClock.bind(self), 1000);
  }
};

omegaup.arena.Arena.prototype.initProblems = function(contest) {
  var self = this;
  self.currentContest = contest;
  self.contestAdmin = contest.admin;
  var problems = contest.problems;
  for (var i = 0; i < problems.length; i++) {
    var problem = problems[i];
    var alias = problem.alias;
    self.problems[alias] = problem;

    $('<th><a href="#problems/' + alias + '" title="' + alias + '">' +
      problems[i].letter + '</a></th>')
        .insertBefore('#ranking-table thead th.total');
    $('<td class="prob_' + alias + '_points"></td>')
        .insertBefore('#ranking-table tbody.user-list-template td.points');
  }
  $('#ranking-table thead th').attr('colspan', '');
  $('#ranking-table tbody.user-list-template .penalty').remove();
};

omegaup.arena.Arena.prototype.updateClock = function() {
  var self = this;
  var countdownTime = self.submissionDeadline || self.finishTime;
  if (self.startTime === null || countdownTime === null) {
    return;
  }

  var date = omegaup.OmegaUp.time().getTime();
  var clock = '';

  if (date < self.startTime.getTime()) {
    clock = '-' +
            omegaup.arena.FormatDelta(self.startTime.getTime() -
                                      (date + omegaup.OmegaUp._deltaTime));
  } else if (date > countdownTime.getTime()) {
    // Contest for self user is over
    clock = '00:00:00';
    clearInterval(self.clockInterval);
    self.clockInterval = null;

    // Show go-to-practice-mode messages on contest end
    if (date > self.finishTime.getTime()) {
      omegaup.UI.warning('<a href="/arena/' + self.options.contestAlias +
                         '/practice/">' +
                         omegaup.T.arenaContestEndedUsePractice + '</a>');
      $('#new-run').hide();
      $('#new-run-practice-msg').show();
      $('#new-run-practice-msg a')
          .prop('href', '/arena/' + self.options.contestAlias + '/practice/');
    }
  } else {
    clock = omegaup.arena.FormatDelta(countdownTime.getTime() -
                                      (date + omegaup.OmegaUp._deltaTime));
  }

  $('#title .clock').html(clock);
};

omegaup.arena.Arena.prototype.updateRunFallback = function(guid) {
  var self = this;
  if (self.socket != null) return;
  setTimeout(function() {
    omegaup.API.runStatus(guid, self.updateRun.bind(self));
  }, 5000);
};

omegaup.arena.Arena.prototype.updateRun = function(run) {
  var self = this;

  self.trackRun(run);

  if (self.socket != null) return;

  if (run.status == 'ready') {
    if (!self.options.isPractice && !self.options.isOnlyProblem &&
        self.options.contestAlias != 'admin') {
      omegaup.API.getRanking(self.options.contestAlias,
                             self.rankingChange.bind(self));
    }
  } else {
    self.updateRunFallback(run.guid);
  }
};

omegaup.arena.Arena.prototype.rankingChange = function(data) {
  var self = this;
  self.onRankingChanged(data);
  if (self.options.scoreboardToken) {
    omegaup.API.getRankingEventsByToken(self.options.contestAlias,
                                        self.options.scoreboardToken,
                                        self.onRankingEvents.bind(self));
  } else {
    omegaup.API.getRankingEvents(self.options.contestAlias,
                                 self.onRankingEvents.bind(self));
  }
};

omegaup.arena.Arena.prototype.onRankingChanged = function(data) {
  var self = this;
  $('#mini-ranking tbody.inserted').remove();
  $('#ranking-table tbody.inserted').remove();

  if (self.removeRecentEventClassTimeout) {
    clearTimeout(self.removeRecentEventClassTimeout);
    self.removeRecentEventClassTimeout = null;
  }

  var ranking = data.ranking || [];
  var newRanking = {};
  var order = {};
  var currentRankingState = {};

  for (var i = 0; i < data.problems.length; i++) {
    order[data.problems[i].alias] = i;
  }

  // Push data to ranking table
  for (var i = 0; i < ranking.length; i++) {
    var rank = ranking[i];
    newRanking[rank.username] = i;

    var r = $('#ranking-table tbody.user-list-template')
                .clone()
                .removeClass('user-list-template')
                .addClass('inserted')
                .addClass('rank-new');

    var username =
        rank.username + ((rank.name == rank.username) ?
                             '' :
                             (' (' + omegaup.UI.escape(rank.name) + ')'));

    $('.user', r).html(username + omegaup.UI.getFlag(rank['country']));

    currentRankingState[username] = {place: rank.place, accepted: {}};

    // Update problem scores.
    var totalRuns = 0;
    for (var alias in order) {
      if (!order.hasOwnProperty(alias)) continue;
      var problem = rank.problems[order[alias]];
      totalRuns += problem.runs;

      var pointsCell = $('.prob_' + alias + '_points', r);
      if (problem.runs == 0) {
        pointsCell.html('-');
      } else if (self.currentContest.show_penalty) {
        pointsCell.html('<div class="points">' +
                        (problem.points ? '+' + problem.points : '0') +
                        '</div>\n' +
                        '<div class="penalty">' + problem.penalty + ' (' +
                        problem.runs + ')</div>');
      } else {
        pointsCell.html('<div class="points">' +
                        (problem.points ? '+' + problem.points : '0') +
                        '</div>\n' +
                        '<div class="penalty">(' + problem.runs + ')</div>');
      }
      pointsCell.removeClass('pending accepted wrong');
      if (problem.runs > 0) {
        if (problem.percent == 100) {
          currentRankingState[username].accepted[problem.alias] = true;
          pointsCell.addClass('accepted');
          if (this.prevRankingState) {
            if (!this.prevRankingState[username] ||
                !this.prevRankingState[username].accepted[problem.alias]) {
              pointsCell.addClass('recent-event');
            }
          }
        } else if (problem.pending) {
          pointsCell.addClass('pending');
        } else if (problem.percent == 0) {
          pointsCell.addClass('wrong');
        }
      }

      if (self.problems[alias]) {
        if (rank.username == omegaup.OmegaUp.username) {
          $('#problems .problem_' + alias + ' .solved')
              .html('(' + problem.points + ' / ' + self.problems[alias].points +
                    ')');
        }
      }
    }

    if (self.currentContest.show_penalty) {
      $('td.points', r)
          .html('<div class="points">' + rank.total.points + '</div>' +
                '<div class="penalty">' + rank.total.penalty + ' (' +
                totalRuns + ')</div>');
    } else {
      $('td.points', r)
          .html('<div class="points">' + rank.total.points + '</div>' +
                '<div class="penalty">(' + totalRuns + ')</div>');
    }
    $('.position', r).html(rank.place).removeClass('recent-event');
    if (this.prevRankingState) {
      if (!this.prevRankingState[username] ||
          this.prevRankingState[username].place > rank.place) {
        $('.position', r).addClass('recent-event');
      }
    }

    $('#ranking-table').append(r);

    // update miniranking
    if (i < 10) {
      r = $('#mini-ranking tbody.user-list-template')
              .clone()
              .removeClass('user-list-template')
              .addClass('inserted');

      $('.position', r).html(rank.place);
      $('.user', r)
          .html('<span title="' + username + '">' + rank.username +
                omegaup.UI.getFlag(rank['country']) + '</span>');
      $('.points', r).html(rank.total.points);
      $('.penalty', r).html(rank.total.penalty);

      $('#mini-ranking').append(r);
    }
  }

  if (data.time) {
    $('#ranking .footer').html(omegaup.OmegaUp.time(data.time));
  }

  this.currentRanking = newRanking;
  this.prevRankingState = currentRankingState;
  self.removeRecentEventClassTimeout = setTimeout(function() {
    $('.recent-event').removeClass('recent-event');
  }, 30000);
};

omegaup.arena.Arena.prototype.onRankingEvents = function(data) {
  var dataInSeries = {};
  var navigatorData = [[this.startTime.getTime(), 0]];
  var series = [];
  var usernames = {};
  this.currentEvents = data;

  // group points by person
  for (var i = 0, l = data.events.length; i < l; i++) {
    var curr = data.events[i];

    // limit chart to top n users
    if (this.currentRanking[curr.username] >
        omegaup.arena.ScoreboardColors.length - 1)
      continue;

    if (!dataInSeries[curr.name]) {
      dataInSeries[curr.name] = [[this.startTime.getTime(), 0]];
      usernames[curr.name] = curr.username;
    }
    dataInSeries[curr.name].push(
        [this.startTime.getTime() + curr.delta * 60 * 1000, curr.total.points]);

    // check if to add to navigator
    if (curr.total.points > navigatorData[navigatorData.length - 1][1]) {
      navigatorData.push([
        this.startTime.getTime() + curr.delta * 60 * 1000,
        curr.total.points
      ]);
    }
  }

  // convert datas to series
  for (var i in dataInSeries) {
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
};

omegaup.arena.Arena.prototype.createChart = function(series, navigatorSeries) {
  if (series.length == 0) return;

  Highcharts.setOptions({colors: omegaup.arena.ScoreboardColors});

  window.chart = new Highcharts.StockChart({
    chart: {renderTo: 'ranking-chart', height: 300, spacingTop: 20},

    xAxis: {
      ordinal: false,
      min: this.startTime.getTime(),
      max: Math.min(this.finishTime.getTime(), Date.now())
    },

    yAxis: {
      showLastLabel: true,
      showFirstLabel: false,
      min: 0,
      max: (function(problems) {
        var total = 0;
        for (var prob in problems) {
          if (!problems.hasOwnProperty(prob)) continue;
          total += parseInt(problems[prob].points, 10);
        }
        return total;
      })(this.problems)
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

  // set legend colors
  var rows = $('#ranking-table tbody.inserted tr');
  for (var r = 0; r < rows.length; r++) {
    $('.legend', rows[r])
        .css({
          'background-color': (r < omegaup.arena.ScoreboardColors.length) ?
                                  omegaup.arena.ScoreboardColors[r] :
                                  'transparent'
        });
  }
};

omegaup.arena.Arena.prototype.flashTitle = function(reset) {
  if (document.title.indexOf('!') === 0) {
    document.title = document.title.substring(2);
  } else if (!reset) {
    document.title = '! ' + document.title;
  }
};

omegaup.arena.Arena.prototype.notify = function(title, message, element, id,
                                                modificationTime) {
  var self = this;

  var lastModified = parseInt((typeof(localStorage) !== 'undefined' &&
                               localStorage.getItem(id)) ||
                                  '0',
                              10) ||
                     0;

  if (self.currentNotifications.hasOwnProperty(id) ||
      lastModified >= modificationTime) {
    return;
  }

  if (self.currentNotifications.timer == null) {
    self.currentNotifications.timer = setInterval(self.flashTitle, 1000);
  }

  self.currentNotifications.count++;

  var gid = $.gritter.add({
    title: title,
    text: message,
    sticky: true,
    before_close: function() {
      if (localStorage) {
        localStorage.setItem(id, modificationTime);
      }
      if (element) {
        window.focus();
        element.scrollIntoView(true);
      }

      self.currentNotifications.count--;
      if (self.currentNotifications.count == 0) {
        clearInterval(self.currentNotifications.timer);
        self.currentNotifications.timer = null;
        self.flashTitle(true);
      }
    }
  });

  self.currentNotifications[id] = gid;

  var audio = document.getElementById('notification_audio');
  if (audio != null) audio.play();
};

omegaup.arena.Arena.prototype.updateClarification = function(clarification) {
  var self = this;
  var r = null;
  if (self.clarifications[clarification.clarification_id]) {
    r = self.clarifications[clarification.clarification_id];
  } else {
    r = $('.clarifications tbody.clarification-list tr.template')
            .clone()
            .removeClass('template')
            .addClass('inserted');

    if (self.contestAdmin) {
      (function(id, answerNode) {
        var responseFormNode =
            $('#create-response-form', answerNode).removeClass('template');
        if (clarification.public == 1) {
          $('#create-response-is-public', responseFormNode)
              .attr('checked', 'checked');
        }
        responseFormNode.submit(function() {
          omegaup.API.updateClarification(
              id, $('#create-response-text', this).val(),
              $('#create-response-is-public', this)[0].checked, function() {
                $('pre', answerNode)
                    .html($('#create-response-text', answerNode).val());
                $('#create-response-text', answerNode).val('');
              });
          return false;
        });
      })(clarification.clarification_id, $('.answer', r));
    }
  }

  $('.contest', r).html(clarification.contest_alias);
  $('.problem', r).html(clarification.problem_alias);
  if (self.contestAdmin) $('.author', r).html(clarification.author);
  $('.time', r)
      .html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S',
                                  clarification.time.getTime()));
  $('.message', r).html(omegaup.UI.escape(clarification.message));
  $('.answer pre', r).html(omegaup.UI.escape(clarification.answer));
  if (clarification.answer) {
    self.answeredClarifications++;
  }

  if (self.contestAdmin != !!clarification.answer) {
    self.notify((clarification.author ? clarification.author + ' - ' : '') +
                    clarification.problem_alias,
                omegaup.UI.escape(clarification.message) +
                    (clarification.answer ?
                         ('<hr/>' + omegaup.UI.escape(clarification.answer)) :
                         ''),
                r[0], 'clarification-' + clarification.clarification_id,
                clarification.time.getTime());
  }

  if (!self.clarifications[clarification.clarification_id]) {
    $('.clarifications tbody.clarification-list').prepend(r);
    self.clarifications[clarification.clarification_id] = r;
  }
};

omegaup.arena.Arena.prototype.clarificationsChange = function(data) {
  var self = this;
  $('.clarifications tr.inserted').remove();
  if (data.clarifications.length > 0 &&
      data.clarifications.length < self.clarificationsRowcount) {
    $('#clarifications-count').html('(' + data.clarifications.length + ')');
  } else if (data.clarifications.length >= self.clarificationsRowcount) {
    $('#clarifications-count').html('(' + data.clarifications.length + '+)');
  }

  var previouslyAnswered = self.answeredClarifications;
  self.answeredClarifications = 0;
  self.clarifications = {};

  for (var i = data.clarifications.length - 1; i >= 0; i--) {
    self.updateClarification(data.clarifications[i]);
  }

  if (self.answeredClarifications > previouslyAnswered &&
      self.activeTab != 'clarifications') {
    $('#clarifications-count').css('font-weight', 'bold');
  }
};

omegaup.arena.Arena.prototype.updateAllowedLanguages = function(lang_array) {
  $('#lang-select option')
      .each(function(index, item) {
        $(item).toggle(lang_array.indexOf($(item).val()) >= 0);
      });
};

omegaup.arena.Arena.prototype.onHashChanged = function() {
  var self = this;
  var tabChanged = false;
  var foundHash = false;
  var tabs = ['summary', 'problems', 'ranking', 'clarifications', 'runs'];

  for (var i = 0; i < tabs.length; i++) {
    if (window.location.hash.indexOf('#' + tabs[i]) == 0) {
      tabChanged = self.activeTab != tabs[i];
      self.activeTab = tabs[i];
      foundHash = true;

      break;
    }
  }

  if (!foundHash) {
    window.location.hash = '#' + self.activeTab;
  }

  var problem = /#problems\/([^\/]+)(\/new-run)?/.exec(window.location.hash);

  if (problem && self.problems[problem[1]]) {
    var newRun = problem[2];
    self.currentProblem = problem = self.problems[problem[1]];

    $('#problem-list .active').removeClass('active');
    $('#problem-list .problem_' + problem.alias).addClass('active');

    function update(problem) {
      $('#summary').hide();
      $('#problem').show();
      $('#problem > .title')
          .html(problem.letter + '. ' + omegaup.UI.escape(problem.title));
      $('#problem .data .points').html(problem.points);
      $('#problem .memory_limit').html(problem.memory_limit / 1024 + 'MB');
      $('#problem .time_limit').html(problem.time_limit / 1000 + 's');
      $('#problem .overall_wall_time_limit')
          .html(problem.overall_wall_time_limit / 1000 + 's');
      $('#problem .statement').html(problem.problem_statement);
      if (!self.myRuns.attached) {
        self.myRuns.attach($('#problem .runs'));
      }
      var karel_langs = ['kp', 'kj'];
      var language_array = problem.languages.split(',');
      if (karel_langs.every(function(x) {
            return language_array.indexOf(x) != -1;
          })) {
        var original_href = $('#problem .karel-js-link a').attr('href');
        var hash_index = original_href.indexOf('#');
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
        $('#problem .source span').html(omegaup.UI.escape(problem.source));
        $('#problem .source').show();
      } else {
        $('#problem .source').hide();
      }
      if (problem.problemsetter) {
        $('#problem .problemsetter a')
            .html(omegaup.UI.escape(problem.problemsetter.name))
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

      function updateRuns(runs) {
        if (runs) {
          for (var i = 0; i < runs.length; i++) {
            self.trackRun(runs[i]);
          }
        }
        self.myRuns.filter_problem(problem.alias);
      }

      if (self.options.isPractice || self.options.isOnlyProblem) {
        omegaup.API.getProblemRuns(problem.alias, {},
                                   function(data) { updateRuns(data.runs); });
      } else {
        updateRuns(problem.runs);
      }

      MathJax.Hub.Queue(
          ['Typeset', MathJax.Hub, $('#problem .statement').get(0)]);
    }

    if (problem.problem_statement) {
      update(problem);
    } else {
      omegaup.API.getProblem(
          self.options.contestAlias, problem.alias, function(problem_ext) {
            problem.source = problem_ext.source;
            problem.problemsetter = problem_ext.problemsetter;
            problem.problem_statement = problem_ext.problem_statement;
            problem.sample_input = problem_ext.sample_input;
            problem.runs = problem_ext.runs;
            update(problem);
          });
    }

    if (newRun) {
      $('#overlay form').hide();
      $('#submit input').show();
      $('#submit #lang-select').show();
      $('#submit').show();
      $('#overlay').show();
      $('#submit textarea[name="code"]').val('');
    }
  } else if (self.activeTab == 'problems') {
    $('#problem').hide();
    $('#summary').show();
    $('#problem-list .active').removeClass('active');
    $('#problem-list .summary').addClass('active');
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
};

omegaup.arena.Arena.prototype.detectShowRun = function() {
  var self = this;
  var showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
  var showRunMatch = window.location.hash.match(showRunRegex);
  if (showRunMatch) {
    $('#overlay form').hide();
    $('#overlay').show();
    omegaup.API.runDetails(showRunMatch[1], function(data) {
      self.displayRunDetails(showRunMatch[1], data);
    });
  }
};

omegaup.arena.Arena.prototype.hideOverlay = function() {
  $('#overlay').hide();
  window.location.hash =
      window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
};

omegaup.arena.Arena.prototype.bindGlobalHandlers = function() {
  var self = this;
  $('#overlay, .close').click(self.onCloseSubmit.bind(self));
};

omegaup.arena.Arena.prototype.onCloseSubmit = function(e) {
  var self = this;
  if (e.target.id === 'overlay' || e.target.className === 'close') {
    $('#submit #clarification').hide();
    self.hideOverlay();
    var code_file = $('#submit-code-file');
    code_file.replaceWith(code_file = code_file.clone(true));
    return false;
  }
};

omegaup.arena.Arena.prototype.updateSummary = function(contest, showTimes) {
  var summary = $('#summary');
  $('.title', summary).html(omegaup.UI.escape(contest.title));
  $('.description', summary).html(omegaup.UI.escape(contest.description));
  var duration = contest.finish_time.getTime() - contest.start_time.getTime();
  $('.window_length', summary)
      .html(omegaup.arena.FormatDelta((contest.window_length * 60000) ||
                                      duration));
  $('.contest_organizer', summary)
      .html('<a href="/profile/' + contest.director + '/">' + contest.director +
            '</a>');

  if (showTimes) {
    $('.start_time', summary)
        .html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S',
                                    contest.start_time.getTime()));
    $('.finish_time', summary)
        .html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S',
                                    contest.finish_time.getTime()));
    $('.scoreboard_cutoff', summary)
        .html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S',
                                    contest.start_time.getTime() +
                                        duration * contest.scoreboard / 100));
  }
};

omegaup.arena.Arena.prototype.displayRunDetails = function(guid, data) {
  var self = this;
  var problemAdmin = data.admin;

  if (data.status == 'error') {
    self.hideOverlay();
    return;
  }

  if (data.compile_error) {
    $('#run-details .compile_error pre')
        .html(omegaup.UI.escape(data.compile_error));
    $('#run-details .compile_error').show();
  } else {
    $('#run-details .compile_error').hide();
    $('#run-details .compile_error pre').html('');
  }
  if (data.logs) {
    $('#run-details .logs pre').html(omegaup.UI.escape(data.logs));
    $('#run-details .logs').show();
  } else {
    $('#run-details .logs').hide();
    $('#run-details .logs pre').html('');
  }
  if (data.source.indexOf('data:') === 0) {
    $('#run-details .source')
        .html('<a href="' + data.source + '" download="data.zip">' +
              omegaup.T['wordsDownload'] + '</a>');
  } else if (data.source == 'lockdownDetailsDisabled') {
    $('#run-details .source')
        .html(omegaup.UI.escape((typeof(sessionStorage) !== 'undefined' &&
                                 sessionStorage.getItem('run:' + guid)) ||
                                omegaup.T['lockdownDetailsDisabled']));
  } else {
    $('#run-details .source').html(omegaup.UI.escape(data.source));
  }

  if (data.judged_by) {
    $('#run-details .judged_by pre').html(omegaup.UI.escape(data.judged_by));
    $('#run-details .judged_by').show();
  } else {
    $('#run-details .judged_by').hide();
    $('#run-details .judged_by pre').html('');
  }

  $('#run-details .cases div').remove();
  $('#run-details .cases table').remove();
  if (problemAdmin) {
    $('#run-details .download a')
        .attr('href', '/api/run/download/run_alias/' + data.guid + '/');
    $('#run-details .download a.details')
        .attr('href',
              '/api/run/download/run_alias/' + data.guid + '/complete/true/');
    $('#run-details .download').show();
  } else {
    $('#run-details .download').hide();
  }

  function numericSort(key) {
    function isDigit(x) { return '0' <= x && x <= '9'; }

    return function(x, y) {
      var i = 0, j = 0;
      for (; i < x[key].length && j < y[key].length; i++, j++) {
        if (isDigit(x[key][i]) && isDigit(x[key][j])) {
          var nx = 0, ny = 0;
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

  if (data.groups && data.groups.length) {
    data.groups.sort(numericSort('group'));
    for (var i = 0; i < data.groups.length; i++) {
      data.groups[i].cases.sort(numericSort('name'));
    }

    var groups =
        $('<table></table>')
            .append(
                $('<thead></thead>')
                    .append(
                        $('<tr></tr>')
                            .append('<th>' + omegaup.T['wordsGroup'] + '</th>')
                            .append('<th>' + omegaup.T['wordsCase'] + '</th>')
                            .append('<th>' + omegaup.T['wordsVerdict'] +
                                    '</th>')
                            .append('<th colspan="3">' +
                                    omegaup.T['rankScore'] + '</th>')
                            .append('<th width="1"></th>')));

    for (var i = 0; i < data.groups.length; i++) {
      var g = data.groups[i];
      var cases = $('<tbody></tbody>').hide();
      groups.append(
          $('<tbody></tbody>')
              .append(
                  $('<tr class="group"></tr>')
                      .append('<th class="center">' +
                              omegaup.UI.escape(g.group) + '</th>')
                      .append('<th colspan="2"></th>')
                      .append('<th class="score">' + g.score + '</th>')
                      .append('<th class="center" width="10">' +
                              (g.max_score !== undefined ? '/' : '') + '</th>')
                      .append('<th>' +
                              (g.max_score !== undefined ? g.max_score : '') +
                              '</th>')
                      .append(
                          $('<td></td>')
                              .append(
                                  $('<span class="collapse glyphicon ' +
                                    'glyphicon-collapse-down"></span>')
                                      .click((function(cases) {
                                        return function(ev) {
                                          var target = $(ev.target);
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
      for (var j = 0; j < g.cases.length; j++) {
        var c = g.cases[j];
        var caseRow =
            $('<tr></tr>')
                .append('<td></td>')
                .append('<td class="center">' + c.name + '</td>')
                .append('<td class="center">' +
                        omegaup.T['verdict' + c.verdict] + '</td>')
                .append('<td class="score">' + c.score + '</td>')
                .append('<td class="center" width="10">' +
                        (c.max_score !== undefined ? '/' : '') + '</td>')
                .append('<td>' +
                        (c.max_score !== undefined ? c.max_score : '') +
                        '</td>');
        cases.append(caseRow);
        if (problemAdmin && c.meta) {
          var metaRow =
              $('<tr class="meta"></tr>')
                  .append('<td colspan="6"><pre>' +
                          JSON.stringify(c.meta, null, 2) + '</pre></td>')
                  .hide();
          caseRow.append(
              $('<td></td>')
                  .append(
                      $('<span class="collapse glyphicon glyphicon-list-alt">' +
                        '</span>')
                          .click((function(metaRow) {
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
};

omegaup.arena.Arena.prototype.trackRun = function(run) {
  var self = this;
  self.runs.trackRun(run);
  if (run.username == omegaup.OmegaUp.username) {
    self.myRuns.trackRun(run);
  }
};

omegaup.arena.RunView = function(arena) {
  var self = this;

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
          var cached_verdict = self.filter_verdict();
          var cached_status = self.filter_status();
          var cached_language = self.filter_language();
          var cached_problem = self.filter_problem();
          var cached_username = self.filter_username();
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
              return a.guid() == b.guid() ? 0 : (a.guid() < b.guid() ? -1 : 1);
            }
            // Newest runs appear on top.
            return b.time().getTime() - a.time().getTime();
          });
        }, self).extend({deferred: true});
  self.display_runs =
      ko.pureComputed(function() {
          var offset = self.filter_offset();
          return self.sorted_runs().slice(offset, offset + self.row_count);
        }, self).extend({deferred: true});
  self.observableRunsIndex = {};
  self.attached = false;
};

omegaup.arena.RunView.prototype.attach = function(elm) {
  var self = this;

  $('.runspager .runspagerprev', elm)
      .click(function() {
        if (self.filter_offset() < self.row_count) {
          self.filter_offset(0);
        } else {
          self.filter_offset(self.filter_offset() - self.row_count);
        }
      });

  $('.runspager .runspagernext', elm)
      .click(function() {
        self.filter_offset(self.filter_offset() + self.row_count);
      });

  $('.runsusername', elm)
      .typeahead(
          {
            minLength: 2,
            highlight: true,
          },
          {
            source: omegaup.UI.typeaheadWrapper(omegaup.API.searchUsers),
            displayKey: 'label',
          })
      .on('typeahead:selected',
          function(elm, item) { self.filter_username(item.value); });

  $('.runsusername-clear', elm)
      .click(function() {
        $('.runsusername', elm).val('');
        self.filter_username('');
      });

  $('.runsproblem', elm)
      .typeahead(
          {
            minLength: 2,
            highlight: true,
          },
          {
            source: omegaup.UI.typeaheadWrapper(function(query, cb) {
              omegaup.API.searchProblems(query,
                                         function(data) { cb(data.results); });
            }),
            displayKey: 'title',
            templates: {
              suggestion: function(elm) {
                return '<strong>' + elm.title + '</strong> (' + elm.alias + ')';
              }
            }
          })
      .on('typeahead:selected',
          function(elm, item) { self.filter_problem(item.alias); });

  $('.runsproblem-clear', elm)
      .click(function() {
        $('.runsproblem', elm).val('');
        self.filter_problem('');
      });

  ko.applyBindings(self, elm[0]);
  self.attached = true;
};

omegaup.arena.RunView.prototype.trackRun = function(run) {
  var self = this;

  if (!self.observableRunsIndex[run.guid]) {
    self.observableRunsIndex[run.guid] =
        new omegaup.arena.ObservableRun(self.arena, run);
    self.runs.push(self.observableRunsIndex[run.guid]);
  } else {
    self.observableRunsIndex[run.guid].update(run);
  }
};

omegaup.arena.RunView.prototype.clear = function(run) {
  var self = this;

  self.runs.removeAll();
  self.observableRunsIndex = {};
};

omegaup.arena.ObservableRun = function(arena, run) {
  var self = this;

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
  self.status_color = ko.pureComputed(self.$status_color, self);
  self.penalty_text = ko.pureComputed(self.$penalty_text, self);
  self.points = ko.pureComputed(self.$points, self);
  self.percentage = ko.pureComputed(self.$percentage, self);
  self.contest_alias_url = ko.pureComputed(self.$contest_alias_url, self);
};

omegaup.arena.ObservableRun.prototype.update = function(run) {
  var self = this;
  for (var p in run) {
    if (!run.hasOwnProperty(p) || !self.hasOwnProperty(p) ||
        !(self[p] instanceof Function)) {
      continue;
    }
    if (self[p]() != run[p]) {
      self[p](run[p]);
    }
  }
};

omegaup.arena.ObservableRun.prototype.$problem_url = function() {
  var self = this;
  return '/arena/problem/' + self.alias() + '/';
};

omegaup.arena.ObservableRun.prototype.$contest_alias_url = function() {
  var self = this;
  return (self.contest_alias() === null) ? '' : '/arena/' +
                                                    self.contest_alias() + '/';
};

omegaup.arena.ObservableRun.prototype.$user_html = function() {
  var self = this;
  return omegaup.UI.getProfileLink(self.username()) +
         omegaup.UI.getFlag(self.country_id());
};

omegaup.arena.ObservableRun.prototype.$time_text = function() {
  var self = this;
  return Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', self.time().getTime());
};

omegaup.arena.ObservableRun.prototype.$runtime_text = function() {
  var self = this;
  if (self.status() == 'ready' && self.verdict() != 'JE' &&
      self.verdict() != 'CE') {
    var prefix = '';
    if (self.verdict() == 'TLE') {
      prefix = '>';
    }
    return prefix + (parseFloat(self.runtime() || '0') / 1000).toFixed(2) +
           ' s';
  } else {
    return '—';
  }
};

omegaup.arena.ObservableRun.prototype.$memory_text = function() {
  var self = this;
  if (self.status() == 'ready' && self.verdict() != 'JE' &&
      self.verdict() != 'CE') {
    var prefix = '';
    if (self.verdict() == 'MLE') {
      prefix = '>';
    }
    return prefix + (parseFloat(self.memory()) / (1024 * 1024)).toFixed(2) +
           ' MB';
  } else {
    return '—';
  }
};

omegaup.arena.ObservableRun.prototype.$penalty_text = function() {
  var self = this;

  if (self.status() == 'ready' && self.verdict() != 'JE' &&
      self.verdict() != 'CE') {
    return self.penalty();
  } else {
    return '—';
  }
};

omegaup.arena.ObservableRun.prototype.$status_text = function() {
  var self = this;

  return self.status() == 'ready' ? omegaup.T['verdict' + self.verdict()] :
                                    self.status();
};

omegaup.arena.ObservableRun.prototype.$status_color = function() {
  var self = this;

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
};

omegaup.arena.ObservableRun.prototype.$points = function() {
  var self = this;
  if (self.contest_score() != null && self.status() == 'ready' &&
      self.verdict() != 'JE' && self.verdict() != 'CE') {
    return parseFloat(self.contest_score() || '0').toFixed(2);
  } else {
    return '—';
  }
};

omegaup.arena.ObservableRun.prototype.$percentage = function() {
  var self = this;
  if (self.status() == 'ready' && self.verdict() != 'JE' &&
      self.verdict() != 'CE') {
    return (parseFloat(self.score() || '0') * 100).toFixed(2) + '%';
  } else {
    return '—';
  }
};

omegaup.arena.ObservableRun.prototype.details = function() {
  var self = this;
  window.location.hash += '/show-run:' + self.guid;
};

omegaup.arena.ObservableRun.prototype.rejudge = function() {
  var self = this;
  omegaup.API.runRejudge(self.guid, false, function(data) {
    if (data.status == 'ok') {
      self.status('rejudging');
      self.arena.updateRunFallback(self.guid);
    }
  });
};

omegaup.arena.ObservableRun.prototype.debug_rejudge = function() {
  var self = this;
  omegaup.API.runRejudge(self.guid, true, function(data) {
    if (data.status == 'ok') {
      self.status('rejudging');
      self.arena.updateRunFallback(self.guid);
    }
  });
};
