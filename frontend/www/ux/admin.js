omegaup.OmegaUp.on('ready', function() {
  var arena = new omegaup.arena.Arena(
      omegaup.arena.GetOptionsFromLocation(window.location));
  var admin = new omegaup.arena.ArenaAdmin(arena);

  $(window).hashchange(arena.onHashChanged.bind(arena));

  Highcharts.setOptions({global: {useUTC: false}});

  if (arena.options.contestAlias === 'admin') {
    $('#runs').show();
    admin.refreshRuns();
    setInterval(function() {
      runsOffset = 0;  // Return pagination to start on refresh
      admin.refreshRuns();
    }, 5 * 60 * 1000);

    // Trigger the event (useful on page load).
    $(window).hashchange();

    $('#loading').fadeOut('slow');
    $('#root').fadeIn('slow');
  } else {
    arena.connectSocket();
    omegaup.API.Contest.details({contest_alias: arena.options.contestAlias})
        .then(function(contest) {
          if (!contest.admin) {
            if (!omegaup.OmegaUp.loggedIn) {
              window.location = '/login/?redirect=' + escape(window.location);
            } else {
              $('#loading').html('404');
            }
            return;
          } else if (arena.options.isPractice && contest.finish_time &&
                     omegaup.OmegaUp.time().getTime() <
                         contest.finish_time.getTime()) {
            window.location =
                window.location.pathname.replace(/\/practice\/.*/, '/');
            return;
          }
          $('#title .contest-title').html(omegaup.UI.escape(contest.title));
          arena.updateSummary(contest);

          arena.submissionGap = parseInt(contest.submission_gap);
          if (!(arena.submissionGap > 0)) arena.submissionGap = 0;

          arena.initClock(contest.start_time, contest.finish_time);
          arena.initProblems(contest);

          for (var idx in contest.problems) {
            var problem = contest.problems[idx];
            var problemName =
                problem.letter + '. ' + omegaup.UI.escape(problem.title);

            arena.problems[problem.alias] = problem;

            var prob = $('#problem-list .template')
                           .clone()
                           .removeClass('template')
                           .addClass('problem_' + problem.alias);
            $('.name', prob)
                .attr('href', '#problems/' + problem.alias)
                .html(problemName);
            $('#problem-list').append(prob);

            $('#clarification select')
                .append('<option value="' + problem.alias + '">' + problemName +
                        '</option>');
            $('select.runsproblem')
                .append('<option value="' + problem.alias + '">' + problemName +
                        '</option>');
          }

          arena.setupPolls();
          admin.refreshRuns();
          if (!arena.socket) {
            setInterval(function() {
              runsOffset = 0;  // Return pagination to start on refresh
              admin.refreshRuns();
            }, 5 * 60 * 1000);
          }

          // Trigger the event (useful on page load).
          $(window).hashchange();

          $('#loading').fadeOut('slow');
          $('#root').fadeIn('slow');
        })
        .fail(function() {
          if (!omegaup.OmegaUp.loggedIn) {
            window.location = '/login/?redirect=' + escape(window.location);
          } else {
            $('#loading').html('404');
          }
        });
  }

  $('#submit select[name="language"]')
      .change(function(e) {
        var lang = $('#submit select[name="language"]').val();
        if (lang == 'cpp11') {
          $('#submit-filename-extension').text('.cpp');
        } else if (lang && lang != 'cat') {
          $('#submit-filename-extension').text('.' + lang);
        } else {
          $('#submit-filename-extension').text();
        }
      });

  $('#submit')
      .submit(function(e) {
        if (!$('#submit textarea[name="code"]').val()) return false;

        $('#submit input').attr('disabled', 'disabled');
        omegaup.API.Run.create({
                         contest_alias: arena.options.contestAlias,
                         problem_alias: arena.currentProblem.alias,
                         language: $('#submit select[name="language"]').val(),
                         source: $('#submit textarea[name="code"]').val(),
                       })
            .then(function(run) {
              run.status = 'new';
              run.alias = arena.currentProblem.alias;
              run.contest_score = null;
              run.time = omegaup.OmegaUp.time();
              run.penalty = 0;
              run.runtime = 0;
              run.memory = 0;
              run.language = $('#submit select[name="language"]').val();
              arena.trackRun(run);
              arena.updateRunFallback(run.guid, run);

              $('#submit input').removeAttr('disabled');
              arena.hideOverlay();
            })
            .fail(function(run) {
              alert(run.error);
              $('#submit input').removeAttr('disabled');
            });

        return false;
      });

  $('#rejudge-problem')
      .click(function() {
        if (confirm('Deseas rejuecear el problema ' +
                    arena.currentProblem.alias + '?')) {
          omegaup.API.rejudgeProblem(arena.currentProblem.alias,
                                     function(x) { admin.refreshRuns(); });
        }
        return false;
      });

  $('#update-problem')
      .submit(function() {
        $('#update-problem input[name="problem_alias"]')
            .val(arena.currentProblem.alias);
        return confirm('Deseas actualizar el problema ' +
                       arena.currentProblem.alias + '?');
      });
});
