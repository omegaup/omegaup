import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import { setLocationHash } from '../location';
import { myRunsStore, runsStore } from './runsStore';
import {
  addRunDetails,
  submitRun,
  trackRunWithDetails,
  submitRunFailed,
  SubmissionRequest,
} from './submissions';

import * as api from '../api';
import * as time from '../time';
import * as ui from '../ui';

import Vue from 'vue';
import arena_Course, { Tabs } from '../components/arena/Coursev2.vue';

OmegaUp.on('ready', async () => {
  const payload = types.payloadParsers.ArenaCoursePayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const locationHash = window.location.hash.substr(1).split('/');
  const activeTab = getSelectedValidTab(locationHash[0]);
  trackRuns();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          allRuns: runsStore.state.runs,
          assignment: payload.assignment,
          course: payload.course,
          currentProblem: payload.currentProblem,
          problems: payload.problems,
          selectedTab: activeTab,
          scoreboard: payload.scoreboard,
          userRuns: myRunsStore.state.runs,
          user: {
            admin: commonPayload.isAdmin,
            loggedIn: commonPayload.isLoggedIn,
            reviewer: commonPayload.isReviewer,
          },
        },
        on: {
          'fetch-run-details': (request: SubmissionRequest) => {
            api.Run.details({ run_alias: request.guid })
              .then((runDetails: types.RunDetailsV2) => {
                addRunDetails({
                  runGUID: request.guid,
                  runDetails,
                });
              })
              .catch(ui.apiError);
          },
          'submit-run': ({
            code,
            language,
          }: {
            code: string;
            language: string;
          }) => {
            const problem = payload.currentProblem;
            if (!problem) {
              return;
            }
            api.Run.create({
              problemset_id: payload.assignment.problemset_id,
              problem_alias: problem.alias,
              language: language,
              source: code,
            })
              .then(time.remoteTimeAdapter)
              .then((response) => {
                submitRun({
                  guid: response.guid,
                  submitDelay: response.submit_delay,
                  language,
                  username: commonPayload.currentUsername,
                  classname: commonPayload.userClassname,
                  problemAlias: problem.alias,
                });
              })
              .catch((run) => {
                submitRunFailed({
                  error: run.error,
                  errorname: run.errorname,
                  run,
                });
              });
          },
        },
      });
    },
  });

  function getSelectedValidTab(tab: string): string | null {
    if (payload.currentProblem && tab === '') {
      return null;
    }
    if (tab === Tabs.Ranking && payload.scoreboard === null) {
      setLocationHash(Tabs.Summary);
      return Tabs.Summary;
    }
    if (Object.values<string>(Tabs).includes(tab)) {
      return tab;
    }
    setLocationHash(Tabs.Summary);
    return Tabs.Summary;
  }

  function trackRuns(): void {
    for (const run of payload.runs) {
      trackRunWithDetails({ run });
    }
  }
});
