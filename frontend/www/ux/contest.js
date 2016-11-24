$(document)
    .ready(function() {
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
          var prob =
              $('.solver-list .template').clone().removeClass('template');
          $('.user', prob)
              .attr('href', '/profile/' + solver.username)
              .html(solver.username);
          $('.language', prob).html(solver.language);
          $('.runtime', prob)
              .html((parseFloat(solver.runtime) / 1000.0).toFixed(2));
          $('.memory', prob)
              .html((parseFloat(solver.memory) / (1024 * 1024)).toFixed(2));
          $('.time', prob)
              .html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S',
                                          solver.time * 1000));
          $('.solver-list').append(prob);
        }

        var language_array = problem.languages.split(',');
        arena.updateAllowedLanguages(language_array);

        if (problem.user.logged_in) {
          omegaup.API.getProblemRuns(problem.alias, {}, function(data) {
            onlyProblemUpdateRuns(data.runs, 'score', 100);
          });
        }

        if (problem.user.admin) {
          admin = new omegaup.arena.ArenaAdmin(arena,
                                               arena.options.onlyProblemAlias);
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
            $('#submit #lang-select').show();
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
              omegaup.API.getClarifications(
                  arena.options.contestAlias, arena.clarificationsOffset,
                  arena.clarificationsRowcount,
                  arena.clarificationsChange.bind(arena));
            });
      }

      function submitRun(contestAlias, problemAlias, lang, code) {
        $('#submit input').attr('disabled', 'disabled');
        omegaup.API.submit(
            contestAlias, problemAlias, lang, code, function(run) {
              if (run.status != 'ok') {
                alert(run.error);
                $('#submit input').removeAttr('disabled');
                return;
              }

              if (arena.options.isLockdownMode && sessionStorage) {
                sessionStorage.setItem('run:' + run.guid, code);
              }

              if (!arena.options.isOnlyProblem) {
                arena.problems[arena.currentProblem.alias].last_submission =
                    omegaup.OmegaUp.time().getTime();
              }

              run.username = omegaup.OmegaUp.username;
              run.status = 'new';
              run.alias = arena.currentProblem.alias;
              run.contest_score = null;
              run.time = omegaup.OmegaUp.time();
              run.penalty = 0;
              run.runtime = 0;
              run.memory = 0;
              run.language = $('#submit select[name="language"]').val();
              arena.updateRun(run);

              $('#submit input').removeAttr('disabled');
              $('#submit textarea[name="code"]').val('');
              var code_file = $('#submit-code-file');
              code_file.replaceWith(code_file = code_file.clone(true));
              arena.hideOverlay();
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
            if (!arena.options.isOnlyProblem &&
                (arena.problems[arena.currentProblem.alias].last_submission +
                     arena.submissionGap * 1000 >
                 omegaup.OmegaUp.time().getTime())) {
              alert('Deben pasar ' + arena.submissionGap +
                    ' segundos entre envios de un mismo problema');
              return false;
            }

            if (!$('#submit select[name="language"]').val()) {
              alert('Debes elegir un lenguaje');
              return false;
            }

            var code = $('#submit textarea[name="code"]').val();
            var file = $('#submit-code-file')[0];
            if (file && file.files && file.files.length > 0) {
              file = file.files[0];
              var reader = new FileReader();

              reader.onload = function(e) {
                submitRun(
                    (arena.options.isPractice || arena.options.isOnlyProblem) ?
                        '' :
                        arena.options.contestAlias,
                    arena.currentProblem.alias,
                    $('#submit select[name="language"]').val(),
                    e.target.result);
              };

              var extension = file.name.split(/\./);
              extension = extension[extension.length - 1];

              if ($('#submit select[name="language"]').val() != 'cat' ||
                  file.type.indexOf('text/') === 0 || extension == 'cpp' ||
                  extension == 'c' || extension == 'java' ||
                  extension == 'txt' || extension == 'hs' ||
                  extension == 'kp' || extension == 'kj' || extension == 'p' ||
                  extension == 'pas' || extension == 'py' ||
                  extension == 'rb') {
                if (file.size >= 10240) {
                  alert('El límite para subir archivos son 10kB');
                  return false;
                }
                reader.readAsText(file, 'UTF-8');
              } else {
                // 100kB _must_ be enough for anybody.
                if (file.size >= 102400) {
                  alert('El límite para subir archivos son 100kB');
                  return false;
                }
                reader.readAsDataURL(file);
              }

              return false;
            }

            if (!code) return false;

            submitRun(
                (arena.options.isPractice || arena.options.isOnlyProblem) ?
                    '' :
                    arena.options.contestAlias,
                arena.currentProblem.alias,
                $('#submit select[name="language"]').val(), code);

            return false;
          });

      $('#clarification')
          .submit(function(e) {
            $('#clarification input').attr('disabled', 'disabled');
            omegaup.API.newClarification(
                arena.options.contestAlias,
                $('#clarification select[name="problem"]').val(),
                $('#clarification textarea[name="message"]').val(),
                function(run) {
                  if (run.status != 'ok') {
                    alert(run.error);
                    $('#clarification input').removeAttr('disabled');
                    return;
                  }
                  arena.hideOverlay();
                  omegaup.API.getClarifications(
                      arena.options.contestAlias, arena.clarificationsOffset,
                      arena.clarificationsRowcount,
                      arena.clarificationsChange.bind(arena));
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
