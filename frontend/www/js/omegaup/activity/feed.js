import Vue from 'vue';
import activity_Feed from '../components/activity/Feed.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  let match =
      /\/([^\/]+)\/([^\/]+)\/activity\/?.*/.exec(window.location.pathname);
  let problemset_type = match[1];
  let problemset_alias = match[2];

  function addMapping(mapping, key, value) {
    if (!mapping.hasOwnProperty(key)) {
      mapping[key] = {};
    }
    if (!mapping[key].hasOwnProperty(value)) {
      mapping[key][value] = true;
    }
  }

  function ActivityReport(report) {
    let events = report.events;
    let userMapping = {};
    let originMapping = {};
    for (let idx in events) {
      if (!events.hasOwnProperty(idx)) continue;
      addMapping(originMapping, events[idx].ip, events[idx].username);
      addMapping(userMapping, events[idx].username, events[idx].ip);
      events[idx].ip = '' + events[idx].ip;
      events[idx].profile_url = '/profile/' + events[idx].username;
      events[idx].time =
          Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', events[idx].time);
      if (events[idx].event.problem) {
        events[idx].event.problem_url =
            '/arena/problem/' + events[idx].event.problem + '/';
      }
    }

    let users = [];
    let sortedUsers = Object.keys(userMapping);
    sortedUsers.sort();
    for (let i = 0; i < sortedUsers.length; i++) {
      let ips = Object.keys(userMapping[sortedUsers[i]]);
      if (ips.length == 1) continue;
      ips.sort();
      users.push({username: sortedUsers[i], ips: ips.join(' ')});
    }

    let origins = [];
    let sortedOrigins = Object.keys(originMapping);
    sortedOrigins.sort();
    for (let i = 0; i < sortedOrigins.length; i++) {
      let users = Object.keys(originMapping[sortedOrigins[i]]);
      if (users.length == 1) continue;
      users.sort();
      for (let j = 0; j < users.length; j++) {
        users[j] = {username: users[j], profile_url: '/profile/' + users[j]};
      }
      origins.push({origin: sortedOrigins[i], usernames: users});
    }
    return {events: events, users: users, origins: origins};
  }

  let activityFeed = new Vue({
    el: '#' + problemset_type + '-activity',
    render: function(createElement) {
      return createElement('omegaup-activity-feed', {
        props: {
          type: this.type,
          alias: this.alias,
          events: this.events,
          users: this.users,
          origins: this.origins
        }
      });
    },
    mounted: function() {
      if (this.type == 'contest') {
        API.Contest.activityReport({'contest_alias': this.alias})
            .then(function(report) {
              let activityReport = ActivityReport(report);
              activityFeed.events = activityReport.events;
              activityFeed.users = activityReport.users;
              activityFeed.origins = activityReport.origins;
            })
            .fail(omegaup.UI.apiError);
      } else if (this.type == 'course') {
        API.Course.activityReport({'course_alias': this.alias})
            .then(function(report) {
              let activityReport = ActivityReport(report);
              activityFeed.events = activityReport.events;
              activityFeed.users = activityReport.users;
              activityFeed.origins = activityReport.origins;
            })
            .fail(omegaup.UI.apiError);
      }
    },
    data: {
      type: problemset_type,
      alias: problemset_alias,
      events: [],
      users: [],
      origins: [],
    },
    components: {
      'omegaup-activity-feed': activity_Feed,
    }
  });
});
