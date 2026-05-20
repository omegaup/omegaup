import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import contest_Intro from '../components/contest/Intro.vue';
import * as ui from '../ui';
import * as api from '../api';
import * as time from '../time';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestIntroPayload();
  const headerPayload = types.payloadParsers.CommonPayload();

  // Adjust the clock in case the local time significantly differs from what is
  // expected.
  payload.contest.start_time = time.remoteDate(payload.contest.start_time);
  if (payload.contest.finish_time) {
    payload.contest.finish_time = time.remoteDate(payload.contest.finish_time);
  }

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-intro': contest_Intro,
    },
    render: function (createElement) {
      return createElement('omegaup-contest-intro', {
        props: {
          requestsUserInformation: payload.requestsUserInformation,
          shouldShowModalToLoginWithRegisteredIdentity:
            payload.shouldShowModalToLoginWithRegisteredIdentity,
          needsBasicInformation: payload.needsBasicInformation,
          contest: payload.contest,
          isLoggedIn: headerPayload.isLoggedIn,
          statement: payload.privacyStatement,
          userBasicInformation: payload.userBasicInformation,
        },
        on: {
          'open-contest': (request: types.ContestAdminDetails): void => {
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
  });
});
