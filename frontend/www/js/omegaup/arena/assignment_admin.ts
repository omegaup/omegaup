import * as api from '../api';
import { Arena, GetOptionsFromLocation } from './arena';
import ArenaAdmin from './admin_arena';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.IntroDetailsPayload();
  const options = GetOptionsFromLocation(window.location);
  const assignmentMatch = /\/course\/([^\/]+)(?:\/assignment\/([^\/]+)\/?)?/.exec(
    window.location.pathname,
  );
  if (assignmentMatch) {
    options.courseName = payload.details.name;
    options.courseAlias = assignmentMatch[1];
    options.assignmentAlias = assignmentMatch[2];
  }

  const arenaInstance = new Arena(options);
  const adminInstance = new ArenaAdmin(arenaInstance);
  adminInstance.refreshRuns();

  // Trigger the event (useful on page load).
  arenaInstance.onHashChanged();

  $('#loading').fadeOut('slow');
  $('#root').fadeIn('slow');

  api.Course.assignmentDetails({
    course: arenaInstance.options.courseAlias,
    assignment: arenaInstance.options.assignmentAlias,
  })
    .then(results => arenaInstance.problemsetLoaded(results))
    .catch(e => arenaInstance.problemsetLoadedError(e));

  window.addEventListener('hashchange', () => arenaInstance.onHashChanged());
});
