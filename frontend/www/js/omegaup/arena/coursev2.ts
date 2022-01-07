import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import { setLocationHash } from '../location';
import { myRunsStore, runsStore } from './runsStore';
import { submitRun, trackRun, submitRunFailed } from './submissions';

import * as api from '../api';
import * as time from '../time';

import Vue from 'vue';
import arena_Course, { Tabs } from '../components/arena/Coursev2.vue';
import { Tabs as problemTabs } from '../components/problem/Detailsv2.vue';

OmegaUp.on('ready', async () => {
  const payload = types.payloadParsers.ArenaCoursePayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  trackRuns();

  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => {
      return {
        selectedTab: null as null | string,
        problemSelectedTab: null as null | string,
      };
    },
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          assignment: payload.assignment,
          course: payload.course,
          currentProblem: payload.currentProblem,
          problems: payload.problems,
          selectedTab: this.selectedTab,
          problemSelectedTab: this.problemSelectedTab,
          scoreboard: payload.scoreboard,
          userRuns: myRunsStore.state.runs,
          allRuns: runsStore.state.runs,
          user: {
            admin: commonPayload.isAdmin,
            loggedIn: commonPayload.isLoggedIn,
            reviewer: commonPayload.isReviewer,
          },
        },
        on: {
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

  getSelectedValidTab();

  function getSelectedValidTab(): void {
    const tab = window.location.hash.substring(1);
    if (payload.currentProblem) {
      arenaCourse.selectedTab = null;
      if (Object.values<string>(problemTabs).includes(tab)) {
        arenaCourse.problemSelectedTab = tab;
        return;
      }
      arenaCourse.problemSelectedTab = problemTabs.Details;
      return;
    }
    if (tab === Tabs.Ranking && payload.scoreboard === null) {
      setLocationHash(Tabs.Summary);
      arenaCourse.selectedTab = Tabs.Summary;
      return;
    }
    if (Object.values<string>(Tabs).includes(tab)) {
      arenaCourse.selectedTab = tab;
      return;
    }
    setLocationHash(Tabs.Summary);
    arenaCourse.selectedTab = Tabs.Summary;
  }

  function trackRuns(): void {
    for (const run of payload.runs) {
      trackRun({ run });
    }
  }
});
