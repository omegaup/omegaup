import Vue from 'vue';
import grader_EphemeralIDE from '../components/arena/EphemeralGrader.vue';

import * as time from '../time';
import * as Util from './util';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  document.body.style.padding = '0';
  const main = document.querySelector('main') as HTMLElement;
  main.style.flex = '1 1 auto';

  const payload = types.payloadParsers.FullIDEPayload();

  if (!payload.ephemeralGraderEnabled) {
    main.innerHTML =
      '<div class="alert alert-danger" role="alert">Ephemeral Grader is currently disabled.</div>';
    return;
  }

  const acceptedLanguages = payload.acceptedLanguages;
  const preferredLanguage = payload.preferredLanguage || acceptedLanguages[0];

  const ideComponent = new Vue({
    el: '#main-container',
    data: () => ({
      nextExecutionTimestamp: null as null | Date,
    }),
    render: function (createElement) {
      return createElement(grader_EphemeralIDE, {
        props: {
          acceptedLanguages,
          preferredLanguage,
          isEmbedded: false,
          initialTheme: Util.MonacoThemes.VSDark,
          nextExecutionTimestamp: this.nextExecutionTimestamp,
        },
        on: {
          'execute-run': () => {
            api.Run.executeForIDE()
              .then(time.remoteTimeAdapter)
              .then((response) => {
                ideComponent.nextExecutionTimestamp =
                  response.nextExecutionTimestamp;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
