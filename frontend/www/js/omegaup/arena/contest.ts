import * as api from '../api';
import { Arena, GetOptionsFromLocation } from './arena';
import { OmegaUp } from '../omegaup';
import * as time from '../time';

function getInputValue(itemSelector: string): string | undefined {
  const element: HTMLInputElement | null = document.querySelector(itemSelector);
  if (element) {
    return element.value;
  }
}

OmegaUp.on('ready', () => {
  const arenaInstance = new Arena(GetOptionsFromLocation(window.location));

  api.Contest.details({
    contest_alias: arenaInstance.options.contestAlias,
  })
    .then(time.remoteTimeAdapter)
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

  document
    .querySelector('#clarification')
    ?.addEventListener('submit', (ev: Event) => {
      ev.preventDefault();
      document
        .querySelectorAll('#clarification input')
        .forEach((input) => input.setAttribute('disabled', 'disabled'));
      api.Clarification.create({
        contest_alias: arenaInstance.options.contestAlias,
        problem_alias: getInputValue('#clarification select[name="problem"]'),
        message: getInputValue('#clarification textarea[name="message"]'),
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
    arenaInstance.onHashChanged();
  });
});
