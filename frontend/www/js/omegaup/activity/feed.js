import Vue from 'vue';
import activity_Feed from '../components/activity/Feed.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as UI from '../ui';

OmegaUp.on('ready', function() {
  let match = /\/([^\/]+)\/([^\/]+)\/activity\/?.*/.exec(
    window.location.pathname,
  );
  let problemsetType = match[1];
  let problemsetAlias = match[2];

  if (problemsetType == 'contest') {
    api.Contest.activityReport({ contest_alias: problemsetAlias })
      .then(function(report) {
        createComponent(problemsetType, problemsetAlias, report.events);
      })
      .catch(UI.apiError);
  } else if (problemsetType == 'course') {
    api.Course.activityReport({ course_alias: problemsetAlias })
      .then(function(report) {
        createComponent(problemsetType, problemsetAlias, report.events);
      })
      .catch(UI.apiError);
  }

  function createComponent(problemsetType, problemsetAlias, report) {
    let activityFeed = new Vue({
      el: '#' + problemsetType + '-activity',
      render: function(createElement) {
        return createElement('omegaup-activity-feed', {
          props: { type: this.type, alias: this.alias, report: this.report },
        });
      },
      data: { type: problemsetType, alias: problemsetAlias, report: report },
      components: {
        'omegaup-activity-feed': activity_Feed,
      },
    });
  }
});
