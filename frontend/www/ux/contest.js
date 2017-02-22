omegaup.OmegaUp.on('ready', function() {
  var arena = new omegaup.arena.Arena(
      omegaup.arena.GetOptionsFromLocation(window.location));
  var admin = null;

  Highcharts.setOptions({global: {useUTC: false}});

  function onlyProblemLoaded(problem) {
    arena.currentProblem = problem;
    arena.myRuns.filter_problem(problem.alias);
    if (!arena.myRuns.attached) {
      arena.myRuns.attach($('#problem .runs'));
    }

    MathJax.Hub.Queue(
        ['Typeset', MathJax.Hub, $('#problem .statement').get(0)]);

    for (var i = 0; i < problem.solvers.length; i++) {
      var solver = problem.solvers[i];
      var prob = $('.solver-list .template').clone().removeClass('template');
      $('.user', prob)
          .attr('href', '/profile/' + solver.username)
          .html(solver.username);
      $('.language', prob).html(solver.language);
      $('.runtime', prob)
          .html((parseFloat(solver.runtime) / 1000.0).toFixed(2));
      $('.memory', prob)
          .html((parseFloat(solver.memory) / (1024 * 1024)).toFixed(2));
      $('.time', prob)
          .html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', solver.time * 1000));
      $('.solver-list').append(prob);
    }

    var language_array = problem.languages.split(',');
    arena.updateAllowedLanguages(language_array);

    if (problem.user.logged_in) {
      omegaup.API.Problem.runs({problem_alias: problem.alias})
          .then(function(data) {
            onlyProblemUpdateRuns(data.runs, 'score', 100);
          });
    }

    if (problem.user.admin) {
      admin =
          new omegaup.arena.ArenaAdmin(arena, arena.options.onlyProblemAlias);
      admin.refreshRuns();
      admin.refreshClarifications();
      setInterval(function() {
        admin.refreshRuns();
        admin.refreshClarifications();
      }, 5 * 60 * 1000);
    }

    // Trigger the event (useful on page load).
    onlyProblemHashChanged();
  }

  function onlyProblemUpdateRuns(runs, score_column, multiplier) {
    $('#problem tbody.added').remove();
    for (var idx in runs) {
      if (!runs.hasOwnProperty(idx)) continue;
      arena.myRuns.trackRun(runs[idx]);
    }
  }

  function onlyProblemHashChanged() {
    var self = this;
    var tabChanged = false;
    var foundHash = false;
    var tabs = ['problems', 'clarifications', 'runs'];

    for (var i = 0; i < tabs.length; i++) {
      if (window.location.hash.indexOf('#' + tabs[i]) == 0) {
        tabChanged = arena.activeTab != tabs[i];
        arena.activeTab = tabs[i];
        foundHash = true;

        break;
      }
    }

    if (!foundHash) {
      window.location.hash = '#' + arena.activeTab;
    }

    if (arena.activeTab == 'problems') {
      if (window.location.hash.indexOf('/new-run') !== -1) {
        if (!omegaup.OmegaUp.loggedIn) {
          window.location = '/login/?redirect=' + escape(window.location);
          return;
        }
        $('#overlay form').hide();
        $('#submit input').show();
        $('#submit').show();
        $('#overlay').show();
        $('#submit textarea[name="code"]').val('');
      }
    }
    arena.detectShowRun();

    if (tabChanged) {
      $('.tabs a.active').removeClass('active');
      $('.tabs a[href="#' + arena.activeTab + '"]').addClass('active');
      $('.tab').hide();
      $('#' + arena.activeTab).show();

      if (arena.activeTab == 'clarifications') {
        $('#clarifications-count').css('font-weight', 'normal');
      }
    }
  }

  if (arena.options.isOnlyProblem) {
    onlyProblemLoaded(JSON.parse(
        document.getElementById('problem-json').firstChild.nodeValue));
  } else {
    arena.connectSocket();
    omegaup.API.getContest(arena.options.contestAlias,
                           arena.contestLoaded.bind(arena));

    $('.clarifpager .clarifpagerprev')
        .click(function() {
          if (arena.clarificationsOffset > 0) {
            arena.clarificationsOffset -= arena.clarificationsRowcount;
            if (arena.clarificationsOffset < 0) {
              arena.clarificationsOffset = 0;
            }

            // Refresh with previous page
            omegaup.API.getClarifications(
                arena.options.contestAlias, arena.clarificationsOffset,
                arena.clarificationsRowcount,
                arena.clarificationsChange.bind(arena));
          }
        });

    $('.clarifpager .clarifpagernext')
        .click(function() {
          arena.clarificationsOffset += arena.clarificationsRowcount;
          if (arena.clarificationsOffset < 0) {
            arena.clarificationsOffset = 0;
          }

          // Refresh with previous page
          omegaup.API.getClarifications(arena.options.contestAlias,
                                        arena.clarificationsOffset,
                                        arena.clarificationsRowcount,
                                        arena.clarificationsChange.bind(arena));
        });
  }

  $('#clarification')
      .submit(function(e) {
        $('#clarification input').attr('disabled', 'disabled');
        omegaup.API.Clarification
            .create({
              contest_alias: arena.options.contestAlias,
              problem_alias: $('#clarification select[name="problem"]').val(),
              message: $('#clarification textarea[name="message"]').val()
            })
            .then(function(run) {
              arena.hideOverlay();
              omegaup.API.getClarifications(
                  arena.options.contestAlias, arena.clarificationsOffset,
                  arena.clarificationsRowcount,
                  arena.clarificationsChange.bind(arena));
            })
            .fail(function(run) { alert(run.error); })
            .always(function() {
              $('#clarification input').removeAttr('disabled');
            });

        return false;
      });

  $(window)
      .hashchange(function(e) {
        if (arena.options.isOnlyProblem) {
          onlyProblemHashChanged(e);
        } else {
          arena.onHashChanged();
        }
      });
});
