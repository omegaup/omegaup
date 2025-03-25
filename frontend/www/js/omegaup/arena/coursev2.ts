import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import { setLocationHash } from '../location';
import { myRunsStore, runsStore } from './runsStore';
import {
  showSubmission,
  submitRun,
  trackRun,
  submitRunFailed,
  SubmissionRequest,
} from './submissions';

import * as api from '../api';
import * as time from '../time';

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
    data: () => {
      return {
        currentRunDetails: null as types.RunDetails | null,
      };
    },
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          allRuns: runsStore.state.runs,
          assignment: payload.assignment,
          course: payload.course,
          currentProblem: payload.currentProblem,
          currentRunDetails: this.currentRunDetails,
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
          'show-run-details': (request: SubmissionRequest) => {
            console.log(request);
            api.Run.details({ run_alias: request.guid })
              .then((runDetails) => {
                console.log(runDetails);
                this.currentRunDetails = showSubmission({
                  request,
                  runDetails,
                });
              })
              .catch((run) => {
                this.currentRunDetails = null;
                submitRunFailed({
                  error: run.error,
                  errorname: run.errorname,
                  run,
                });
              });
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
      trackRun({ run });
    }
  }
});
