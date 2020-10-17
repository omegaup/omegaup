import * as api from '../api';
import { Arena, GetOptionsFromLocation } from './arena';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseDetailsPayload();
  const options = GetOptionsFromLocation(window.location);
  const assignmentMatch = /\/course\/([^/]+)(?:\/assignment\/([^/]+)\/?)?/.exec(
    window.location.pathname,
  );
  if (assignmentMatch) {
    options.courseName = payload.details.name;
    options.courseAlias = assignmentMatch[1];
    options.assignmentAlias = assignmentMatch[2];
  }

  const arenaInstance = new Arena(options);
  api.Course.assignmentDetails({
    course: arenaInstance.options.courseAlias,
    assignment: arenaInstance.options.assignmentAlias,
  })
    .then((results) => arenaInstance.problemsetLoaded(results))
    .catch((e) => arenaInstance.problemsetLoadedError(e));

  window.addEventListener('hashchange', () => arenaInstance.onHashChanged());
});
