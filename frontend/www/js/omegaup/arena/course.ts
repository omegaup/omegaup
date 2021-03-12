import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as time from '../time';
import { getOptionsFromLocation } from './location';

import Vue from 'vue';
import arena_Course, { ActiveProblem } from '../components/arena/Course.vue';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { navigateToProblem } from './navigation';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.AssignmentDetailsPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';
  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => ({
      popUpDisplayed: PopupDisplayed.None,
      problemInfo: null as types.ProblemInfo | null,
      problem: null as ActiveProblem | null,
      problems: payload.currentAssignment
        .problems as types.NavbarProblemsetProblem[],
      showNewClarificationPopup: false,
      guid: null as null | string,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          course: payload.courseDetails,
          currentAssignment: payload.currentAssignment,
          popupDisplayed: this.popUpDisplayed,
          problemInfo: this.problemInfo,
          problem: this.problem,
          problems: this.problems,
          showNewClarificationPopup: this.showNewClarificationPopup,
          showRanking: payload.showRanking,
          shouldShowFirstAssociatedIdentityRunWarning:
            payload.shouldShowFirstAssociatedIdentityRunWarning,
          activeTab,
          guid: this.guid,
        },
        on: {
          'navigate-to-problem': ({ problem, runs }: ActiveProblem) => {
            navigateToProblem({
              problem,
              runs,
              target: arenaCourse,
              problems: this.problems,
            });
          },
        },
      });
    },
  });

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  Object.assign(arenaCourse, getOptionsFromLocation(window.location.hash));
});
