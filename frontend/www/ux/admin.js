omegaup.OmegaUp.on('ready', function() {
  var arenaInstance = new arena.Arena(
    arena.GetOptionsFromLocation(window.location),
  );
  var adminInstance = new arena.ArenaAdmin(arenaInstance);

  window.addEventListener(
    'hashchange',
    arenaInstance.onHashChanged.bind(arenaInstance),
  );

  Highcharts.setOptions({ global: { useUTC: false } });

  if (arenaInstance.options.contestAlias === 'admin') {
    $('#runs').show();
    adminInstance.refreshRuns();
    setInterval(function() {
      runsOffset = 0; // Return pagination to start on refresh
      adminInstance.refreshRuns();
    }, 5 * 60 * 1000);

    // Trigger the event (useful on page load).
    arenaInstance.onHashChanged();

    $('#loading').fadeOut('slow');
    $('#root').fadeIn('slow');
  } else {
    omegaup.API.Contest.adminDetails({
      contest_alias: arenaInstance.options.contestAlias,
    })
      .then(function(contest) {
        if (!contest.admin) {
          if (!omegaup.OmegaUp.loggedIn) {
            window.location = '/login/?redirect=' + escape(window.location);
          } else {
            window.location = '/';
          }
          return;
        } else if (
          arenaInstance.options.isPractice &&
          contest.finish_time &&
          Date.now() < contest.finish_time.getTime()
        ) {
          window.location = window.location.pathname.replace(
            /\/practice\/.*/,
            '/',
          );
          return;
        }
        $('#title .contest-title').html(
          omegaup.UI.escape(omegaup.UI.contestTitle(contest)),
        );
        arenaInstance.updateSummary(contest);

        arenaInstance.submissionGap = parseInt(contest.submission_gap);
        if (!(arenaInstance.submissionGap > 0)) arenaInstance.submissionGap = 0;

        arenaInstance.initProblemsetId(contest);
        arenaInstance.initClock(contest.start_time, contest.finish_time);
        arenaInstance.initProblems(contest);
        for (var idx in contest.problems) {
          var problem = contest.problems[idx];
          var problemName =
            problem.letter + '. ' + omegaup.UI.escape(problem.title);

          arenaInstance.problems[problem.alias] = problem;
          arenaInstance.elements.navBar.problemsList.push({
            alias: problem.alias,
            text: problemName,
            score: '',
            active: false,
          });

          $('#clarification select[name=problem]').append(
            '<option value="' +
              problem.alias +
              '">' +
              problemName +
              '</option>',
          );
          $('select.runsproblem').append(
            '<option value="' +
              problem.alias +
              '">' +
              problemName +
              '</option>',
          );
        }

        omegaup.API.Contest.users({
          contest_alias: arenaInstance.options.contestAlias,
        })
          .then(function(data) {
            for (var ind in data.users) {
              var user = data.users[ind];
              var receiver = user.is_owner
                ? omegaup.T.wordsPublic
                : omegaup.UI.escape(user.username);
              $('#clarification select[name=user]').append(
                '<option value="' +
                  omegaup.UI.escape(user.username) +
                  '">' +
                  receiver +
                  '</option>',
              );
            }
          })
          .fail(omegaup.UI.ignoreError);

        arenaInstance.setupPolls();
        adminInstance.refreshRuns();
        if (!arenaInstance.socket) {
          setInterval(function() {
            runsOffset = 0; // Return pagination to start on refresh
            adminInstance.refreshRuns();
          }, 5 * 60 * 1000);
        }

        // Trigger the event (useful on page load).
        arenaInstance.onHashChanged();

        $('#loading').fadeOut('slow');
        $('#root').fadeIn('slow');
      })
      .fail(function() {
        if (!omegaup.OmegaUp.loggedIn) {
          window.location = '/login/?redirect=' + escape(window.location);
        } else {
          window.location = '/';
        }
      });
  }

  $('#submit select[name="language"]').on('change', function(e) {
    var lang = $('#submit select[name="language"]').val();
    if (lang.startsWith('cpp')) {
      $('#submit-filename-extension').text('.cpp');
    } else if (lang.startsWith('c-')) {
      $('#submit-filename-extension').text('.c');
    } else if (lang.startsWith('py')) {
      $('#submit-filename-extension').text('.py');
    } else if (lang && lang != 'cat') {
      $('#submit-filename-extension').text('.' + lang);
    } else {
      $('#submit-filename-extension').text();
    }
  });

  $('#submit').on('submit', function(e) {
    if (!$('#submit textarea[name="code"]').val()) return false;

    $('#submit input').attr('disabled', 'disabled');
    omegaup.API.Run.create({
      contest_alias: arenaInstance.options.contestAlias,
      problem_alias: arenaInstance.currentProblem.alias,
      language: $('#submit select[name="language"]').val(),
      source: $('#submit textarea[name="code"]').val(),
    })
      .then(function(run) {
        run.status = 'new';
        run.alias = arenaInstance.currentProblem.alias;
        run.contest_score = null;
        run.time = new Date();
        run.penalty = 0;
        run.runtime = 0;
        run.memory = 0;
        run.language = $('#submit select[name="language"]').val();
        arenaInstance.trackRun(run);
        arenaInstance.updateRunFallback(run.guid, run);

        $('#submit input').prop('disabled', false);
        arenaInstance.hideOverlay();
      })
      .fail(function(run) {
        alert(run.error);
        $('#submit input').prop('disabled', false);
      });

    return false;
  });

  $('#rejudge-problem').on('click', function() {
    if (
      confirm(
        'Deseas rejuecear el problema ' +
          arenaInstance.currentProblem.alias +
          '?',
      )
    ) {
      omegaup.API.Problem.rejudge({
        problem_alias: arenaInstance.currentProblem.alias,
      })
        .then(function() {
          adminInstance.refreshRuns();
        })
        .fail(omegaup.UI.ignoreError);
    }
    return false;
  });

  $('#update-problem').on('submit', function() {
    $('#update-problem input[name="problem_alias"]').val(
      arenaInstance.currentProblem.alias,
    );
    return confirm(
      'Deseas actualizar el problema ' +
        arenaInstance.currentProblem.alias +
        '?',
    );
  });
});
