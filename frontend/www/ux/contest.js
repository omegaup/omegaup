omegaup.OmegaUp.on('ready', function() {
  var arena = new omegaup.arena.Arena(
      omegaup.arena.GetOptionsFromLocation(window.location));
  var admin = null;

  Highcharts.setOptions({global: {useUTC: false}});

  function onlyProblemLoaded(problem) {
    arena.renderProblem(problem);

    arena.myRuns.filter_problem(problem.alias);
    arena.myRuns.attach($('#problem .runs'));

    for (var i = 0; i < problem.solvers.length; i++) {
      var solver = problem.solvers[i];
      var prob = $('.solver-list .template').clone().removeClass('template');
      $('.user', prob)
          .attr('href', '/profile/' + solver.username)
          .text(solver.username);
      $('.language', prob).text(solver.language);
      $('.runtime', prob)
          .text((parseFloat(solver.runtime) / 1000.0).toFixed(2));
      $('.memory', prob)
          .text((parseFloat(solver.memory) / (1024 * 1024)).toFixed(2));
      $('.time', prob)
          .text(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', solver.time * 1000));
      $('.solver-list').append(prob);
    }

    if (problem.user.logged_in) {
      omegaup.API.Problem.runs({problem_alias: problem.alias})
          .then(function(data) {
            onlyProblemUpdateRuns(data.runs, 'score', 100);
          })
          .fail(omegaup.UI.apiError);
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
        arena.codeEditor.code = arena.currentProblem.template;
        arena.codeEditor.refresh();
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
    onlyProblemLoaded(
        JSON.parse(document.getElementById('payload').firstChild.nodeValue));
  } else {
    omegaup.API.Contest.details({contest_alias: arena.options.contestAlias})
        .then(arena.problemsetLoaded.bind(arena))
        .fail(omegaup.UI.ignoreError);

    $('.clarifpager .clarifpagerprev')
        .on('click', function() {
          if (arena.clarificationsOffset > 0) {
            arena.clarificationsOffset -= arena.clarificationsRowcount;
            if (arena.clarificationsOffset < 0) {
              arena.clarificationsOffset = 0;
            }

            // Refresh with previous page
            arena.refreshClarifications();
          }
        });

    $('.clarifpager .clarifpagernext')
        .on('click', function() {
          arena.clarificationsOffset += arena.clarificationsRowcount;
          if (arena.clarificationsOffset < 0) {
            arena.clarificationsOffset = 0;
          }

          // Refresh with previous page
          arena.refreshClarifications();
        });
  }

  $('#clarification')
      .on('submit', function(e) {
        $('#clarification input').attr('disabled', 'disabled');
        omegaup.API.Clarification
            .create({
              contest_alias: arena.options.contestAlias,
              problem_alias: $('#clarification select[name="problem"]').val(),
              message: $('#clarification textarea[name="message"]').val()
            })
            .then(function(run) {
              arena.hideOverlay();
              arena.refreshClarifications();
            })
            .fail(function(run) { alert(run.error); })
            .always(function() {
              $('#clarification input').prop('disabled', false);
            });

        return false;
      });

  window.addEventListener('hashchange', function() {
    if (arena.options.isOnlyProblem) {
      onlyProblemHashChanged();
    } else {
      arena.onHashChanged();
    }
  });
});
