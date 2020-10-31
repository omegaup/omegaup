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
    const solverTemplate = document.querySelector('.solver-list .template');
    const solverList = document.querySelector('.solver-list');
    if (solverTemplate && solverList) {
      for (const solver of problem.solvers ?? []) {
        const prob = <HTMLElement>solverTemplate.cloneNode(true);
        prob.classList.remove('template');
        prob
          .querySelector('.user')
          ?.setAttribute('href', '/profile/${solver.username}');
        ui.setItemText(prob, '.user', solver.username);
        ui.setItemText(prob, '.language', solver.language);
        ui.setItemText(prob, '.runtime', (solver.runtime / 1000.0).toFixed(2));
        ui.setItemText(
          prob,
          '.memory',
          (solver.memory / (1024 * 1024)).toFixed(2),
        );
        ui.setItemText(prob, '.time', time.formatTimestamp(solver.time));
        solverList.appendChild(prob);
      }
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
        document
          .querySelectorAll('#overlay form:not([data-run-submit])')
          .forEach(
            (element) => ((<HTMLElement>element).style.display = 'none'),
          );
        document
          .querySelectorAll('#overlay')
          .forEach(
            (element) => ((<HTMLElement>element).style.display = 'block'),
          );
      }
    }
    arenaInstance.detectShowRun();

    if (tabChanged) {
      document.querySelectorAll('.tab').forEach((tab) => {
        (<HTMLElement>tab).style.display = 'none';
        tab.classList.remove('active');
      });
      document
        .querySelectorAll('.tabs a[href="#' + arenaInstance.activeTab + '"]')
        .forEach((element) => element.classList.add('active'));
      document
        .querySelectorAll('#' + arenaInstance.activeTab)
        .forEach((element) => ((<HTMLElement>element).style.display = 'block'));

      if (arenaInstance.activeTab == 'clarifications') {
        const clarificationsCountElement = <HTMLElement>(
          document.querySelector('#clarifications-count')
        );
        if (clarificationsCountElement) {
          clarificationsCountElement.style.fontWeight = 'normal';
        }
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

    document
      .querySelector('.clarifpager .clarifpagerprev')
      ?.addEventListener('click', () => {
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

    document
      .querySelector('.clarifpager .clarifpagernext')
      ?.addEventListener('click', () => {
        arenaInstance.clarificationsOffset +=
          arenaInstance.clarificationsRowcount;
        if (arenaInstance.clarificationsOffset < 0) {
          arenaInstance.clarificationsOffset = 0;
        }

        // Refresh with previous page
        arenaInstance.refreshClarifications();
      });
  }

  document.querySelector('#clarification')?.addEventListener('submit', () => {
    document
      .querySelectorAll('#clarification input')
      .forEach((input) => input.setAttribute('disabled', 'disabled'));
    api.Clarification.create({
      contest_alias: arenaInstance.options.contestAlias,
      problem_alias: ui.getInputValue(
        null,
        '#clarification select[name="problem"]',
      ),
      message: ui.getInputValue(
        null,
        '#clarification textarea[name="message"]',
      ),
    })
      .then(() => {
        arenaInstance.hideOverlay();
        arenaInstance.refreshClarifications();
      })
      .catch((e) => {
        alert(e.error);
      })
      .finally(() => {
        document
          .querySelectorAll('#clarification input')
          .forEach((input) => input.removeAttribute('disabled'));
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
