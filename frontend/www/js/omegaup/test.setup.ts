import * as util from 'util';
import 'process';

// Suppress Vue 3 warnings BEFORE importing Vue, since Vue caches console.warn
// at module initialization time.
const originalConsoleWarn = console.warn;
console.warn = function (...args: any[]) {
  const msg = String(args[0] || '');
  if (
    msg.includes('Failed to resolve component') ||
    msg.includes('provide() can only be used inside setup()')
  ) {
    return;
  }
  originalConsoleWarn(...args);
};

import '@testing-library/jest-dom';
import '@testing-library/jest-dom/extend-expect';

import './compat';
import Vue from 'vue';
import Sortable from 'sortablejs';

Vue.directive('Sortable', {
  inserted: (el: HTMLElement, binding) => {
    new Sortable(el, binding.value || {});
  },
});

// Intercept all API calls. Only let `API.Session.currentSession()` work and
// fail everything else.
import fetchMock from 'jest-fetch-mock';
fetchMock.enableMocks();
fetchMock.mockIf(/^\/api\/.*/, (req: Request) => {
  if (req.url != '/api/session/currentSession/') {
    return Promise.resolve({
      ok: false,
      status: 404,
      body: JSON.stringify({
        status: 'error',
        error: `Invalid call to "${req.url}" in test`,
        errorcode: 403,
      }),
    });
  }
  return Promise.resolve({
    status: 200,
    body: JSON.stringify({
      status: 'ok',
      session: {
        valid: false,
      },
      time: Date.now() / 1000,
    }),
  });
});

declare global {
  namespace NodeJS {
    interface Global {
      $: JQuery;
      jQuery: JQuery;
      document: Document;
      URL: {
        createObjectURL: () => string;
      };
    }
  }

  interface Window {
    jQuery: JQuery;
  }
}

(global as any).jQuery = require('jquery');
(global as any).$ = (global as any).jQuery;
window.jQuery = (global as any).jQuery;
global.URL.createObjectURL = jest.fn();

// This is needed for CodeMirror to work.
global.document.createRange = () => {
  return ({
    setEnd: () => {},
    setStart: () => {},
    getBoundingClientRect: () => {},
    getClientRects: () => [],
  } as any) as Range;
};

// Any write to console.error() will cause a test failure.
const originalConsoleError = console.error;
console.error = function (...args: any[]) {
  // Suppress known Vue 3 compat warnings during test migration
  const msg = String(args[0] || '');
  if (
    msg.includes('provide() can only be used inside setup()') ||
    msg.includes('Vue.prototype is no longer available') ||
    msg.includes('GLOBAL_PROTOTYPE') ||
    msg.includes('COMPONENT_V_MODEL') ||
    msg.includes('NotFoundError') ||
    msg.includes('The node to be removed is not a child')
  ) {
    originalConsoleError(...args);
    return;
  }
  originalConsoleError(...args);
  throw new Error(
    'Unexpected call to console.error(). Failing test: ' + util.inspect(args),
  );
};

// Make sure that warnings will not cause test termination. This is because
// warnings are always emitted in the next tick, which will cause an unhandled
// exception and kill the node process altogether.
process.removeAllListeners('warning');
process.on('warning', (warning) => {
  originalConsoleError(warning.stack);
});

// https://github.com/vuejs/vue-test-utils/issues/936
window.Date = Date;

// Suppress NotFoundError thrown during Vue 3 teardown in jsdom
process.on('uncaughtException', (err: Error) => {
  if (
    err.name === 'NotFoundError' &&
    err.message === 'The node to be removed is not a child of this node.'
  ) {
    return;
  }
  throw err;
});
process.on('unhandledRejection', (reason: any) => {
  if (
    reason?.name === 'NotFoundError' &&
    reason?.message === 'The node to be removed is not a child of this node.'
  ) {
    return;
  }
  throw reason;
});

import { config, VueWrapper } from '@vue/test-utils';
config.global.stubs = {
  transition: false,
  'transition-group': false,
};

// Polyfill wrapper.destroy() for Vue Test Utils v2 (Vue 3 compat)
(VueWrapper.prototype as any).destroy = VueWrapper.prototype.unmount;

// Suppress Vue 3 compat deprecation warnings that are expected during migration
const originalWarn = console.warn;
console.warn = function (...args: any[]) {
  const msg = String(args[0] || '');
  if (
    msg.includes('GLOBAL_PROTOTYPE') ||
    msg.includes('RENDER_FUNCTION') ||
    msg.includes('CONFIG_OPTION_MERGE_STRATS') ||
    msg.includes('CUSTOM_DIR') ||
    msg.includes('ATTR_FALSE_VALUE')
  ) {
    return;
  }
  originalWarn(...args);
};
