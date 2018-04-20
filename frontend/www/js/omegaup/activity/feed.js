import Vue from 'vue';
import activity_Feed from '../components/activity/Feed.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  let match =
      /\/([^\/]+)\/([^\/]+)\/activity\/?.*/.exec(window.location.pathname);
  let problemset_type = match[1];
  let problemset_alias = match[2];

  if (problemset_type == 'contest') {
    API.Contest.activityReport({'contest_alias': problemset_alias})
        .then(function(report) {
          createComponent(problemset_type, problemset_alias, report);
        })
        .fail(omegaup.UI.apiError);
  } else if (problemset_type == 'course') {
    API.Course.activityReport({'course_alias': problemset_alias})
        .then(function(report) {
          createComponent(problemset_type, problemset_alias, report);
        })
        .fail(omegaup.UI.apiError);
  }

  function createComponent(problemset_type, problemset_alias, report) {
    let activityFeed = new Vue({
      el: '#' + problemset_type + '-activity',
      render: function(createElement) {
        return createElement(
            'omegaup-activity-feed',
            {props: {type: this.type, alias: this.alias, report: this.report}});
      },
      data: {type: problemset_type, alias: problemset_alias, report: report},
      components: {
        'omegaup-activity-feed': activity_Feed,
      }
    });
  }
});
