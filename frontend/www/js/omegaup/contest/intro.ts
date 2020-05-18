import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import contest_Intro from '../components/contest/Intro.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestIntroPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  const contestIntro = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-contest-intro', {
        props: {
          requestsUserInformation: payload.requestsUserInformation,
          needsBasicInformation: payload.needsBasicInformation,
          contest: payload.contest,
          isLoggedIn: headerPayload.isLoggedIn,
          statement: payload.privacyStatement,
        },
        on: {
          'open-contest': (request: omegaup.Contest): void => {
            // Explicitly join the contest.
            api.Contest.open(request)
              .then(() => {
                window.location.reload();
              })
              .catch(ui.apiError);
          },
          'request-access': (contestAlias: string): void => {
            api.Contest.registerForContest({ contest_alias: contestAlias })
              .then(() => {
                window.location.reload();
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    components: {
      'omegaup-contest-intro': contest_Intro,
    },
  });
});
