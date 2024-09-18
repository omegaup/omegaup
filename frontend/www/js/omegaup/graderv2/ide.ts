import Vue from 'vue';
import grader_EphemeralIDE from '../components/arena/EphemeralGrader.vue';

import * as Util from './util';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  // TODO: implement typescript payload parser
  document.body.style.padding = '0';
  const main = document.querySelector('main') as HTMLElement;
  main.style.flex = '1 1 auto';

  new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement(grader_EphemeralIDE, {
        props: {
          isEmbedded: false,
          initialTheme: Util.MonacoThemes.VSDark,
        },
      });
    },
  });
});
