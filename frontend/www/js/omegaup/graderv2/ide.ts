import Vue from 'vue';
import EphemeralGrader from '../components/arena/EphemeralGrader.vue';
import { OmegaUp } from '../omegaup';
import * as Util from './util';

OmegaUp.on('ready', () => {
  // no payload is necessary for full IDE
  // we need to manipulate the dom to make the IDE full screen
  document.body.style.padding = '0';
  const main = document.querySelector('main') as HTMLElement;
  main.style.flex = '1 1 auto';
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
