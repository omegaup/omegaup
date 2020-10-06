import * as api from '../api';
import { types } from '../api_types';
import { Arena, GetOptionsFromLocation } from './arena';
import ArenaAdmin from './admin_arena';
import { OmegaUp } from '../omegaup';
import * as time from '../time';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const arenaInstance = new Arena(GetOptionsFromLocation(window.location));

  const onlyProblemLoaded = (problem: types.ProblemDetailsPayload) => {
    arenaInstance.renderProblem(problem);

    for (const solver of problem.solvers ?? []) {
      const prob = $('.solver-list .template').clone().removeClass('template');
      $('.user', prob)
        .attr('href', '/profile/' + solver.username)
        .text(solver.username);
      $('.language', prob).text(solver.language);
      $('.runtime', prob).text((solver.runtime / 1000.0).toFixed(2));
      $('.memory', prob).text((solver.memory / (1024 * 1024)).toFixed(2));
      $('.time', prob).text(time.formatTimestamp(solver.time));
      $('.solver-list').append(prob);
    }

    if (problem.user.logged_in) {
      api.Problem.runs({ problem_alias: problem.alias })
        .then((result) => {
          onlyProblemUpdateRuns(result.runs, 'score', 100);
        })
        .catch(ui.apiError);
    }

    if (problem.user.admin) {
      const adminInstance = new ArenaAdmin(arenaInstance);
      adminInstance.refreshRuns();
      adminInstance.refreshClarifications();
      setInterval(() => {
        adminInstance.refreshRuns();
        adminInstance.refreshClarifications();
      }, 5 * 60 * 1000);
    }

    // Trigger the event (useful on page load).
    onlyProblemHashChanged();
  };

  const onlyProblemUpdateRuns = (
    runs: types.Run[],
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    scoreColumn: string,
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    multiplier: number,
  ): void => {
    for (const run of runs) {
      arenaInstance.trackRun(run);
    }
  };

  const onlyProblemHashChanged = () => {
    let tabChanged = false;
    let foundHash = false;

    for (const tab of ['problems', 'solution', 'clarifications', 'runs']) {
      if (window.location.hash.indexOf(`#${tab}`) == 0) {
        tabChanged = arenaInstance.activeTab != tab;
        arenaInstance.activeTab = tab;
        foundHash = true;

        break;
      }
    }

    if (!foundHash) {
      // Change the URL to the deafult tab but don't break the back button.
      window.history.replaceState({}, '', `#${arenaInstance.activeTab}`);
    }

    if (arenaInstance.activeTab == 'problems') {
      if (window.location.hash.indexOf('/new-run') !== -1) {
        if (!OmegaUp.loggedIn) {
          window.location.href = `/login/?redirect=${escape(
            window.location.href,
          )}`;
          return;
        }
        $('#overlay form:not([data-run-submit])').hide();
        $('#overlay').show();
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
  };

  if (arenaInstance.options.isOnlyProblem) {
    onlyProblemLoaded(types.payloadParsers.ProblemDetailsPayload());
  } else {
    api.Contest.details({
      contest_alias: arenaInstance.options.contestAlias,
    })
      .then((result) => arenaInstance.problemsetLoaded(result))
      .catch((e) => arenaInstance.problemsetLoadedError(e));

    $('.clarifpager .clarifpagerprev').on('click', () => {
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

    $('.clarifpager .clarifpagernext').on('click', () => {
      arenaInstance.clarificationsOffset +=
        arenaInstance.clarificationsRowcount;
      if (arenaInstance.clarificationsOffset < 0) {
        arenaInstance.clarificationsOffset = 0;
      }

      // Refresh with previous page
      arenaInstance.refreshClarifications();
    });
  }

  $('#clarification').on('submit', () => {
    $('#clarification input').attr('disabled', 'disabled');
    api.Clarification.create({
      contest_alias: arenaInstance.options.contestAlias,
      problem_alias: $('#clarification select[name="problem"]').val(),
      message: $('#clarification textarea[name="message"]').val(),
    })
      .then(() => {
        arenaInstance.hideOverlay();
        arenaInstance.refreshClarifications();
      })
      .catch((e) => {
        alert(e.error);
      })
      .finally(() => {
        $('#clarification input').prop('disabled', false);
      });

    return false;
  });

  window.addEventListener('hashchange', () => {
    if (arenaInstance.options.isOnlyProblem) {
      onlyProblemHashChanged();
    } else {
      arenaInstance.onHashChanged();
    }
  });
});
