import * as api from '../api';
import { Arena, GetOptionsFromLocation } from './arena';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const arenaInstance = new Arena(GetOptionsFromLocation(window.location));

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
    arenaInstance.clarificationsOffset += arenaInstance.clarificationsRowcount;
    if (arenaInstance.clarificationsOffset < 0) {
      arenaInstance.clarificationsOffset = 0;
    }

    // Refresh with previous page
    arenaInstance.refreshClarifications();
  });

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
    arenaInstance.onHashChanged();
  });
});
