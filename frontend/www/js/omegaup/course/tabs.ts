import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import course_Tabs, { Tab } from '../components/course/Tabs.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseTabsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const locationHashTab = window.location.hash.substr(1);
  let selectedTab = Tab.Public;
  for (const tab of Object.values(Tab)) {
    if (locationHashTab === tab) {
      selectedTab = locationHashTab;
      break;
    }
  }
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-tabs': course_Tabs,
    },
    render: function (createElement) {
      return createElement('omegaup-course-tabs', {
        props: {
          courses: payload.courses,
          loggedIn: commonPayload.isLoggedIn,
          selectedTab,
          hasVisitedSection: payload.hasVisitedSection,
        },
      });
    },
  });
});
