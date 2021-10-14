import * as api from '../api';
import T from '../lang';
import { Arena, GetOptionsFromLocation } from './arena';
import ArenaAdmin from './admin_arena';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import * as time from '../time';

OmegaUp.on('ready', () => {
  const arenaInstance = new Arena(GetOptionsFromLocation(window.location));
  const adminInstance = new ArenaAdmin(arenaInstance);

  window.addEventListener('hashchange', () => arenaInstance.onHashChanged());

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
      .then(time.remoteTimeAdapter)
      .then((contest) => {
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

        $('#title .contest-title').text(ui.contestTitle(contest));
        arenaInstance.updateSummary(contest);

        arenaInstance.submissionGap = contest.submissions_gap;
        if (!(arenaInstance.submissionGap > 0)) arenaInstance.submissionGap = 0;

        arenaInstance.initProblemsetId(contest);
        arenaInstance.initProblems(contest);
        arenaInstance.initClock(contest.start_time, contest.finish_time, null);
        if (contest.problems) {
          for (const idx in contest.problems) {
            const problem = contest.problems[idx];
            const problemName = `${problem.letter}. ${ui.escape(
              problem.title,
            )}`;

            arenaInstance.problems[problem.alias] = {
              ...problem,
              languages: problem.languages
                .split(',')
                .filter((language) => language !== ''),
            };
            if (arenaInstance.navbarProblems) {
              arenaInstance.navbarProblems.problems.push({
                alias: problem.alias,
                acceptsSubmissions: true,
                text: problemName,
                bestScore: 0,
                maxScore: 0,
                hasRuns: false,
              });
            }

            $('#clarification select[name=problem]').append(
              `<option value="${problem.alias}">${problemName}</option>`,
            );
            $('select.runsproblem').append(
              `<option value="${problem.alias}">${problemName}</option>`,
            );
          }
        }

        api.Contest.users({
          contest_alias: arenaInstance.options.contestAlias,
        })
          .then((data) => {
            for (const ind in data.users) {
              const user = data.users[ind];
              const receiver = user.is_owner
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
