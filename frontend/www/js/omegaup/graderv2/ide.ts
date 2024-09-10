import Vue from 'vue';
import grader_EphemeralIDE from '../components/arena/EphemeralGrader.vue';
import { OmegaUp } from '../omegaup';
import * as Util from './util';

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
