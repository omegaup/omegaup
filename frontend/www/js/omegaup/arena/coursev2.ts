import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import arena_Course, { Tabs } from '../components/arena/Coursev2.vue';

OmegaUp.on('ready', async () => {
  const payload = types.payloadParsers.ArenaCoursePayload();

  const locationHash = window.location.hash.substr(1).split('/');
  const activeTab = getSelectedValidTab(locationHash[0]);
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          course: payload.course,
          assignment: payload.assignment,
          problems: payload.problems,
          currentProblem: payload.currentProblem,
          selectedTab: activeTab,
          scoreboard: payload.scoreboard,
        },
      });
    },
  });

  function getSelectedValidTab(tab: string): string | null {
    if (payload.currentProblem && tab === '') {
      return null;
    }
    if (tab === Tabs.Ranking && payload.scoreboard === null) {
      return Tabs.Summary;
    }
    return Object.values<string>(Tabs).includes(tab) ? tab : Tabs.Summary;
  }
});
