import Vue from 'vue';
import activity_Feed from '../components/activity/Feed.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  let match =
      /\/([^\/]+)\/([^\/]+)\/activity\/?.*/.exec(window.location.pathname);
  let problemsetType = match[1];
  let problemsetAlias = match[2];

  if (problemsetType == 'contest') {
    API.Contest.activityReport({'contest_alias': problemsetAlias})
        .then(function(report) {
          createComponent(problemsetType, problemsetAlias, report.events);
        })
        .fail(omegaup.UI.apiError);
  } else if (problemsetType == 'course') {
    API.Course.activityReport({'course_alias': problemsetAlias})
        .then(function(report) {
          createComponent(problemsetType, problemsetAlias, report.events);
        })
        .fail(omegaup.UI.apiError);
  }

  function createComponent(problemsetType, problemsetAlias, report) {
    let activityFeed = new Vue({
      el: '#' + problemsetType + '-activity',
      render: function(createElement) {
        return createElement(
            'omegaup-activity-feed',
            {props: {type: this.type, alias: this.alias, report: this.report}});
      },
      data: {type: problemsetType, alias: problemsetAlias, report: report},
      components: {
        'omegaup-activity-feed': activity_Feed,
      }
    });
  }
});
