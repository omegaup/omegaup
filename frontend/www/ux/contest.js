omegaup.OmegaUp.on('ready', function() {
  var arenaInstance = new arena.Arena(
    arena.GetOptionsFromLocation(window.location),
  );
  var adminInstance = null;

  Highcharts.setOptions({ global: { useUTC: false } });

  function onlyProblemLoaded(problem) {
    arenaInstance.renderProblem(problem);

    arenaInstance.myRuns.filter_problem(problem.alias);
    arenaInstance.myRuns.attach($('#problem .runs'));

    for (var i = 0; i < problem.solvers.length; i++) {
      var solver = problem.solvers[i];
      var prob = $('.solver-list .template')
        .clone()
        .removeClass('template');
      $('.user', prob)
        .attr('href', '/profile/' + solver.username)
        .text(solver.username);
      $('.language', prob).text(solver.language);
      $('.runtime', prob).text(
        (parseFloat(solver.runtime) / 1000.0).toFixed(2),
      );
      $('.memory', prob).text(
        (parseFloat(solver.memory) / (1024 * 1024)).toFixed(2),
      );
      $('.time', prob).text(
        Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', solver.time * 1000),
      );
      $('.solver-list').append(prob);
    }

    if (problem.user.logged_in) {
      omegaup.API.Problem.runs({ problem_alias: problem.alias })
        .then(function(data) {
          onlyProblemUpdateRuns(data.runs, 'score', 100);
        })
        .fail(omegaup.UI.apiError);
    }

    if (problem.user.admin) {
      adminInstance = new arena.ArenaAdmin(
        arenaInstance,
        arenaInstance.options.onlyProblemAlias,
      );
      adminInstance.refreshRuns();
      adminInstance.refreshClarifications();
      setInterval(function() {
        adminInstance.refreshRuns();
        adminInstance.refreshClarifications();
      }, 5 * 60 * 1000);
    }

    // Trigger the event (useful on page load).
    onlyProblemHashChanged();
  }

  function onlyProblemUpdateRuns(runs, score_column, multiplier) {
    $('#problem tbody.added').remove();
    for (var idx in runs) {
      if (!runs.hasOwnProperty(idx)) continue;
      arenaInstance.myRuns.trackRun(runs[idx]);
    }
  }

  function onlyProblemHashChanged() {
    var self = this;
    var tabChanged = false;
    var foundHash = false;
    var tabs = ['problems', 'solution', 'clarifications', 'runs'];

    for (var i = 0; i < tabs.length; i++) {
      if (window.location.hash.indexOf('#' + tabs[i]) == 0) {
        tabChanged = arenaInstance.activeTab != tabs[i];
        arenaInstance.activeTab = tabs[i];
        foundHash = true;

        break;
      }
    }

    if (!foundHash) {
      window.location.hash = '#' + arenaInstance.activeTab;
    }

    if (arenaInstance.activeTab == 'problems') {
      if (window.location.hash.indexOf('/new-run') !== -1) {
        if (!omegaup.OmegaUp.loggedIn) {
          window.location = '/login/?redirect=' + escape(window.location);
          return;
        }
        $('#overlay form').hide();
        $('#submit input').show();
        $('#submit').show();
        $('#overlay').show();
        arenaInstance.codeEditor.code = arenaInstance.currentProblem.template;
        arenaInstance.codeEditor.refresh();
      }
    }
    arenaInstance.detectShowRun();

    if (tabChanged) {
      $('.tabs a.active').removeClass('active');
      $('.tabs a[href="#' + arenaInstance.activeTab + '"]').addClass('active');
      $('.tab').hide();
      $('#' + arenaInstance.activeTab).show();

      if (arenaInstance.activeTab == 'clarifications') {
        $('#clarifications-count').css('font-weight', 'normal');
      }
    }
  }

  if (arenaInstance.options.isOnlyProblem) {
    onlyProblemLoaded(
      JSON.parse(document.getElementById('payload').firstChild.nodeValue),
    );
  } else {
    omegaup.API.Contest.details({
      contest_alias: arenaInstance.options.contestAlias,
    })
      .then(arenaInstance.problemsetLoaded.bind(arenaInstance))
      .fail(omegaup.UI.ignoreError);

    $('.clarifpager .clarifpagerprev').on('click', function() {
      if (arenaInstance.clarificationsOffset > 0) {
        arenaInstance.clarificationsOffset -=
          arenaInstance.clarificationsRowcount;
        if (arenaInstance.clarificationsOffset < 0) {
          arenaInstance.clarificationsOffset = 0;
        }

        // Refresh with previous page
        arenaInstance.refreshClarifications();
      }
    });

    $('.clarifpager .clarifpagernext').on('click', function() {
      arenaInstance.clarificationsOffset +=
        arenaInstance.clarificationsRowcount;
      if (arenaInstance.clarificationsOffset < 0) {
        arenaInstance.clarificationsOffset = 0;
      }

      // Refresh with previous page
      arenaInstance.refreshClarifications();
    });
  }

  $('#clarification').on('submit', function(e) {
    $('#clarification input').attr('disabled', 'disabled');
    omegaup.API.Clarification.create({
      contest_alias: arenaInstance.options.contestAlias,
      problem_alias: $('#clarification select[name="problem"]').val(),
      message: $('#clarification textarea[name="message"]').val(),
    })
      .then(function(run) {
        arenaInstance.hideOverlay();
        arenaInstance.refreshClarifications();
      })
      .fail(function(run) {
        alert(run.error);
      })
      .always(function() {
        $('#clarification input').prop('disabled', false);
      });

    return false;
  });

  window.addEventListener('hashchange', function() {
    if (arenaInstance.options.isOnlyProblem) {
      onlyProblemHashChanged();
    } else {
      arenaInstance.onHashChanged();
    }
  });
});
