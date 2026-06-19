import { Experiments, EventListenerList, OmegaUp } from './omegaup.ts';
import * as API from './api';
import * as Markdown from './markdown';
import * as Time from './time';
import * as UI from './ui';
import T from './lang';
export { API, EventListenerList, Experiments, Markdown, OmegaUp, T, Time, UI };

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

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker
      .register('/sw.js')
      .catch((error) => {
        console.error(
          'Service Worker registration failed:',
          error,
        );
      });
  });
}
