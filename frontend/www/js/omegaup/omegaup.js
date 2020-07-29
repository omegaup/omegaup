import { Experiments, EventListenerList, OmegaUp } from './omegaup.ts';
import * as API from './api';
import * as Markdown from './markdown';
import * as Time from './time';
import * as Typeahead from './typeahead';
import * as UI from './ui';
import T from './lang';
export {
  API,
  EventListenerList,
  Experiments,
  Markdown,
  OmegaUp,
  T,
  Time,
  Typeahead,
  UI,
};

if (
  document.readyState === 'complete' ||
  (document.readyState !== 'loading' && !document.documentElement.doScroll)
) {
  OmegaUp._onDocumentReady();
} else {
  document.addEventListener(
    'DOMContentLoaded',
    OmegaUp._onDocumentReady.bind(OmegaUp),
  );
}
