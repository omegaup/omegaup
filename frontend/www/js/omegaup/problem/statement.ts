import Vue from 'vue';

import { types } from '../api_types';
import problem_StatementEdit from '../components/problem/StatementEdit.vue';
import { OmegaUp } from '../omegaup';

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

OmegaUp.on('ready', () => {
  // Ask the user if they want to restore the last draft
  const markdownStatement =
    localStorage.getItem('wmdinput') || defaultStatement;

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
            localStorage.setItem('wmdinput', statement.markdown);
          },
        },
      });
    },
  });
});
