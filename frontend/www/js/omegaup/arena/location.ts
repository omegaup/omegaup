import { types } from '../api_types';
import { PopupDisplayed } from '../components/problem/Details.vue';
import clarificationsStore from './clarificationsStore';

export interface LocationOptions {
  problem: types.NavbarProblemsetProblem | null;
  popupDisplayed: PopupDisplayed;
  guid: null | string;
  problemAlias: null | string;
  showNewClarificationPopup: boolean;
}

export function getOptionsFromLocation(location: string): LocationOptions {
  const response: LocationOptions = {
    problem: null,
    popupDisplayed: PopupDisplayed.None,
    guid: null,
    problemAlias: null,
    showNewClarificationPopup: false,
  };

  // Location string is of the forms:
  // - `#problems/${alias}`
  // - `#problems/${alias}/new-run`
  // - `#problems/${alias}/show-run:xyz`
  // - `#clarifications/${alias}/new`
  // and all the matching forms in the following regex
  const match = /#(?<tab>\w+)\/(?<alias>[^/]+)(?:\/(?<popup>[^/]+))?/g.exec(
    location,
  );
  switch (match?.groups?.tab) {
    case 'problems':
      response.problem = {
        alias: match?.groups?.alias,
        text: '',
        acceptsSubmissions: true,
        bestScore: 0,
        maxScore: 0,
        hasRuns: false,
      };
      response.problemAlias = match?.groups?.alias;
      if (match.groups.popup === 'new-run') {
        response.popupDisplayed = PopupDisplayed.RunSubmit;
      } else if (match.groups.popup?.startsWith('show-run')) {
        response.guid = match.groups.popup.split(':')[1];
        response.problemAlias = response.problem.alias;
        response.popupDisplayed = PopupDisplayed.RunDetails;
      }
      break;
    case 'clarifications':
      if (match.groups.popup === 'new') {
        response.showNewClarificationPopup = true;
      } else if (match.groups.alias?.startsWith('clarification-')) {
        clarificationsStore.commit(
          'selectClarificationId',
          parseInt(match.groups.alias.split('-')[1]),
        );
      }
      break;
    default:
      response.popupDisplayed = PopupDisplayed.None;
      response.showNewClarificationPopup = false;
  }

  return response;
}
