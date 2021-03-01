import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as time from '../time';

import Vue from 'vue';
import arena_Course from '../components/arena/Course.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.AssignmentDetailsPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';
  console.log(payload);
  console.log(activeTab);

  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problem: null as ActiveProblem | null,
      problems: payload.problems as types.NavbarProblemsetProblem[],
      clarifications: payload.clarifications,
      popupDisplayed: PopupDisplayed.None,
      showNewClarificationPopup: false,
      guid: null as null | string,
    }),
  });
});
