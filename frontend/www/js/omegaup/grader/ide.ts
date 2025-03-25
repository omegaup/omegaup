import Vue from 'vue';
import grader_EphemeralIDE from '../components/arena/EphemeralGrader.vue';

import * as Util from './util';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  document.body.style.padding = '0';
  const main = document.querySelector('main') as HTMLElement;
  main.style.flex = '1 1 auto';

  const payload = types.payloadParsers.FullIDEPayload();
  const acceptedLanguages = payload.acceptedLanguages;
  const preferredLanguage = payload.preferredLanguage || acceptedLanguages[0];

  new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement(grader_EphemeralIDE, {
        props: {
          acceptedLanguages,
          preferredLanguage,
          isEmbedded: false,
          initialTheme: Util.MonacoThemes.VSDark,
        },
      });
    },
  });
});
