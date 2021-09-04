import { PopupDisplayed } from '../components/problem/Details.vue';
import clarificationsStore from './clarificationsStore';

export interface LocationOptions {
  popupDisplayed: PopupDisplayed;
  showNewClarificationPopup: boolean;
}

export function getOptionsFromLocation(location: string): LocationOptions {
  const response: LocationOptions = {
    popupDisplayed: PopupDisplayed.None,
    showNewClarificationPopup: false,
  };

  // Location string is of the forms:
  // - `#problems/${alias}`
  // - `#problems/${alias}/new-run`
  // - `#clarifications/${alias}/new`
  // and all the matching forms in the following regex
  const match = /#(?<tab>\w+)\/(?<alias>[^/]+)(?:\/(?<popup>[^/]+))?/g.exec(
    location,
  );
  switch (match?.groups?.tab) {
    case 'problems':
      if (match.groups.popup === 'new-run') {
        response.popupDisplayed = PopupDisplayed.RunSubmit;
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
