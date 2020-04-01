import { Experiments, EventListenerList, OmegaUp } from './omegaup.ts';
import API from './api.js';
import * as UI from './ui.js';
import T from './lang';
export { API, EventListenerList, Experiments, OmegaUp, T, UI };

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
