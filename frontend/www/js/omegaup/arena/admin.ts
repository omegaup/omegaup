import * as api from '../api';
import T from '../lang';
import { Arena, GetOptionsFromLocation } from './arena';
import ArenaAdmin from './admin_arena';
import { omegaup, OmegaUp } from '../omegaup';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const arenaInstance = new Arena(GetOptionsFromLocation(window.location));
  const adminInstance = new ArenaAdmin(arenaInstance);

  window.addEventListener('hashchange', (e: Event) =>
    arenaInstance.onHashChanged(),
  );

  if (arenaInstance.options.contestAlias === 'admin') {
    $('#runs').show();
    adminInstance.refreshRuns();
    setInterval(() => {
      adminInstance.refreshRuns();
    }, 5 * 60 * 1000);

    // Trigger the event (useful on page load).
    arenaInstance.onHashChanged();

    $('#loading').fadeOut('slow');
    $('#root').fadeIn('slow');
  } else {
    api.Contest.adminDetails({
      contest_alias: arenaInstance.options.contestAlias,
    })
      .then(contest => {
        if (!contest.admin) {
          if (!OmegaUp.loggedIn) {
            window.location.href = `/login/?redirect=${encodeURIComponent(
              window.location.pathname,
            )}`;
          } else {
            window.location.href = '/';
          }
          return;
        }

        if (
          arenaInstance.options.isPractice &&
          contest.finish_time &&
          Date.now() < contest.finish_time.getTime()
        ) {
          window.location.href = window.location.pathname.replace(
            /\/practice\/.*/,
            '/',
          );
          return;
        }

        $('#title .contest-title').text(ui.contestTitle(contest));
        arenaInstance.updateSummary(<omegaup.Contest>contest);

        arenaInstance.submissionGap = contest.submissions_gap;
        if (!(arenaInstance.submissionGap > 0)) arenaInstance.submissionGap = 0;

        arenaInstance.initProblemsetId(contest);
        arenaInstance.initProblems(contest);
        arenaInstance.initClock(contest.start_time, contest.finish_time, null);
        for (var idx in contest.problems) {
          var problem = contest.problems[idx];
          var problemName = `${problem.letter}. ${ui.escape(problem.title)}`;

          arenaInstance.problems[problem.alias] = problem;
          if (arenaInstance.navbarProblems) {
            arenaInstance.navbarProblems.problems.push({
              alias: problem.alias,
              acceptsSubmissions: true,
              text: problemName,
              bestScore: 0,
              maxScore: 0,
              active: false,
            });
          }

          $('#clarification select[name=problem]').append(
            `<option value="${problem.alias}">${problemName}</option>`,
          );
          $('select.runsproblem').append(
            `<option value="${problem.alias}">${problemName}</option>`,
          );
        }

        api.Contest.users({
          contest_alias: arenaInstance.options.contestAlias,
        })
          .then(data => {
            for (var ind in data.users) {
              var user = data.users[ind];
              var receiver = user.is_owner
                ? T.wordsPublic
                : ui.escape(user.username);
              $('#clarification select[name=user]').append(
                `<option value="${ui.escape(
                  user.username,
                )}">${receiver}</option>`,
              );
            }
          })
          .catch(ui.ignoreError);

        arenaInstance.setupPolls();
        adminInstance.refreshRuns();
        if (!arenaInstance.socket) {
          setInterval(() => {
            adminInstance.refreshRuns();
          }, 5 * 60 * 1000);
        }

        // Trigger the event (useful on page load).
        arenaInstance.onHashChanged();

        $('#loading').fadeOut('slow');
        $('#root').fadeIn('slow');
      })
      .catch(() => {
        if (!OmegaUp.loggedIn) {
          window.location.href = `/login/?redirect=${encodeURIComponent(
            window.location.pathname,
          )}`;
        } else {
          window.location.href = '/';
        }
      });
  }

  $('#submit select[name="language"]').on('change', () => {
    var lang = String($('#submit select[name="language"]').val());
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

  $('#submit').on('submit', () => {
    if (!$('#submit textarea[name="code"]').val()) return false;

    $('#submit input').attr('disabled', 'disabled');
    api.Run.create({
      contest_alias: arenaInstance.options.contestAlias,
      problem_alias: arenaInstance.currentProblem.alias,
      language: $('#submit select[name="language"]').val(),
      source: $('#submit textarea[name="code"]').val(),
    })
      .then(run => {
        arenaInstance.trackRun({
          status: 'new',
          guid: run.guid,
          alias: arenaInstance.currentProblem.alias,
          time: new Date(),
          score: 0,
          submit_delay: 0,
          penalty: 0,
          runtime: 0,
          memory: 0,
          username: arenaInstance.options.payload.currentUsername,
          classname: arenaInstance.options.payload.userClassname,
          country: arenaInstance.options.payload.userCountry,
          language: String($('#submit select[name="language"]').val()),
          verdict: 'JE',
        });
        arenaInstance.updateRunFallback(run.guid);

        $('#submit input').prop('disabled', false);
        arenaInstance.hideOverlay();
      })
      .catch(run => {
        alert(run.error);
        $('#submit input').prop('disabled', false);
      });

    return false;
  });

  $('#rejudge-problem').on('click', () => {
    if (
      confirm(
        `Deseas rejuecear el problema ${arenaInstance.currentProblem.alias}?`,
      )
    ) {
      api.Problem.rejudge({
        problem_alias: arenaInstance.currentProblem.alias,
      })
        .then(() => {
          adminInstance.refreshRuns();
        })
        .catch(ui.ignoreError);
    }
    return false;
  });

  $('#update-problem').on('submit', () => {
    $('#update-problem input[name="problem_alias"]').val(
      arenaInstance.currentProblem.alias,
    );
    return confirm(
      `Deseas actualizar el problema ${arenaInstance.currentProblem.alias}?`,
    );
  });
});
