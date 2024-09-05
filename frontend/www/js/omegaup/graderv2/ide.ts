import Vue from 'vue';
import EphemeralGrader from '../components/arena/EphemeralGrader.vue';
import { OmegaUp } from '../omegaup';
import * as Util from './util';

OmegaUp.on('ready', () => {
  // no payload is necessary for full IDE
  new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement(EphemeralGrader, {
        props: {
          canSubmit: false,
          canRun: true,
          acceptedLanguages: Object.values(Util.supportedLanguages).map(
            (languageInfo) => languageInfo.language,
          ),
          preferredLanguage: 'cpp17-gcc',
          isEmbedded: false,
          theme: 'vs-dark',
        },
      });
    },
  });
});
