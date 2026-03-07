import Vue from 'vue';

import { types } from '../api_types';
import problem_StatementEdit from '../components/problem/StatementEdit.vue';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import T from '../lang';
import * as localStorageHelper from '../localStorage';

const defaultStatement = `# Descripción

Esta es la descripción del problema. Inventa una historia creativa.
Puedes utilizar matemáticas inline para hacer $x_i, y_i$, o $z_i$ o incluso:
$$x=\\frac{b\\pm \\sqrt{b^2 -4ac}}{2a}$$

# Entrada

Aquí va la descripción de la entrada del problema.

# Salida

Esta es la descripción de la salida esperada.

# Ejemplo

||input
1
2
||output
Case #1: 3
||description
Explicación
||input
5
10
||output
Case #2: 15
||end

# Límites

* Aquí
* Van
* Los
* Límites`;

const STORAGE_KEY = 'omegaup:editor:statement:draft';
const STORAGE_TIMESTAMP_KEY = 'omegaup:editor:statement:timestamp';

function isValidMarkdown(content: string): boolean {
  if (typeof content !== 'string' || content.trim().length === 0) {
    return false;
  }

  if (!/[a-zA-Z0-9]/.test(content)) {
    return false;
  }

  if (content.trim().length < 5) {
    return false;
  }

  return true;
}

function getValidDraft(): string | null {
  const storedDraft = localStorageHelper.safeGetItem(STORAGE_KEY);
  const storedTimestamp = localStorageHelper.safeGetItem(STORAGE_TIMESTAMP_KEY);

  if (!storedDraft) {
    return null;
  }

  if (!isValidMarkdown(storedDraft)) {
    console.warn('Invalid or corrupted draft detected, ignoring');
    localStorageHelper.clearDraft(STORAGE_KEY, STORAGE_TIMESTAMP_KEY);
    return null;
  }

  if (localStorageHelper.isDraftExpired(storedTimestamp)) {
    console.warn('Draft expired, using default');
    localStorageHelper.clearDraft(STORAGE_KEY, STORAGE_TIMESTAMP_KEY);
    return null;
  }

  return storedDraft;
}

function saveDraft(markdown: string): void {
  localStorageHelper.saveDraftWithTimestamp(
    STORAGE_KEY,
    STORAGE_TIMESTAMP_KEY,
    markdown,
  );
}

OmegaUp.on('ready', () => {
  const validDraft = getValidDraft();
  const markdownStatement = validDraft || defaultStatement;

  const isStorageAvailable = localStorageHelper.checkLocalStorageAvailability();
  if (!isStorageAvailable) {
    console.warn('localStorage is unavailable - draft autosave is disabled');

    setTimeout(() => {
      ui.warning(T.localStorageUnavailableInBrowsingMode);
    }, 1000);
  }

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-problem-statementedit': problem_StatementEdit,
    },
    render: function (createElement) {
      return createElement('omegaup-problem-statementedit', {
        props: {
          alias: 'problema',
          title: 'Tu problema',
          source: 'fuente',
          problemsetter: {
            classname: 'user-rank-unranked',
            name: 'tu-usuario',
            username: 'tu_usuario',
          } as types.ProblemsetterInfo,
          statement: {
            markdown: markdownStatement,
            language: 'es',
            images: {},
          } as types.ProblemStatement,
          markdownType: 'statements',
          showEditControls: false,
        },
        on: {
          'update:statement': (statement: types.ProblemStatement) => {
            saveDraft(statement.markdown);
          },
        },
      });
    },
  });
});
