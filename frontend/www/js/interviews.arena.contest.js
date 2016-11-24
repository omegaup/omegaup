$(document)
    .ready(function() {
      var arena = new omegaup.arena.Arena({
        contestAlias:
            /\/interview\/([^\/]+)\/arena/.exec(window.location.pathname)[1],
        isInterview: true
      });
      var admin = null;

      omegaup.API.getContest(arena.options.contestAlias,
                             arena.contestLoaded.bind(arena));

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
              run.time = new Date;
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

      $(window).hashchange(arena.onHashChanged.bind(arena));
    });
