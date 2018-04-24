<template>
  <div class="post">
    <div class="copy">
      <h1><a v-bind:href="`/${type}/${alias}/`">{{ alias }}</a> — {{ T.wordsActivityReport }}</h1>
      <p>{{ wordsReportSummary }}</p><!-- Nav tabs -->
      <ul class="nav nav-tabs"
          role="tablist">
        <li class="active"
            role="presentation">
          <a aria-controls="report"
              data-toggle="tab"
              href="#report"
              role="tab">{{ T.wordsActivityReportReport }}</a>
        </li>
        <li role="presentation">
          <a aria-controls="users"
              data-toggle="tab"
              href="#users"
              role="tab">{{ T.wordsActivityReportUsers }}</a>
        </li>
        <li role="presentation">
          <a aria-controls="origins"
              data-toggle="tab"
              href="#origins"
              role="tab">{{ T.wordsActivityReportOrigins }}</a>
        </li>
      </ul><!-- Tab panes -->
      <div class="tab-content">
        <!-- id-lint off -->
        <div class="tab-pane active"
             id="report"
             name="report"
             role="tabpanel">
          <!-- id-lint on -->
          <table class="table">
            <thead>
              <tr>
                <th>{{ T.profileUsername }}</th>
                <th>{{ T.wordsTime }}</th>
                <th>{{ T.wordsActivityReportOrigin }}</th>
                <th colspan="2">{{ T.wordsActivityReportEvent }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in events">
                <td>
                  <a v-bind:href=
                  "`/profile/${event.username}`"><strong><omegaup-user-username v-bind:classname=
                  "event.classname"
                                         v-bind:username=
                                         "event.username"></omegaup-user-username></strong></a>
                </td>
                <td>{{ event.time }}</td>
                <td>{{ event.ip }}</td>
                <td>{{ event.event.name }}</td>
                <td><span v-if="event.event.problem"><a v-bind:href=
                "`/arena/problem/${event.event.problem}/`">{{ event.event.problem
                }}</a></span></td>
              </tr>
            </tbody>
          </table>
        </div><!-- id-lint off -->
        <div class="tab-pane"
             id="users"
             name="users"
             role="tabpanel">
          <!-- id-lint on -->
          <p v-if="users &lt;= 0">{{ T.wordsActivityReportNoDuplicatesForUsers }}</p>
          <table class="table"
                 v-else="">
            <caption>
              {{ T.wordsActivityReportDuplicatesForUsersDescription }}
            </caption>
            <thead>
              <tr>
                <th>{{ T.profileUsername }}</th>
                <th>{{ T.wordsActivityReportOrigin }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users">
                <td>
                  <a v-bind:href=
                  "`/profile/${user.username}`"><strong><omegaup-user-username v-bind:classname=
                  "user.classname"
                                         v-bind:username=
                                         "user.username"></omegaup-user-username></strong></a>
                </td>
                <td><span v-for="ip in user.ips">{{ ip }}&nbsp;</span></td>
              </tr>
            </tbody>
          </table>
        </div><!-- id-lint off -->
        <div class="tab-pane"
             id="origins"
             name="origins"
             role="tabpanel">
          <!-- id-lint on -->
          <p v-if="origins &lt;= 0">{{ T.wordsActivityReportNoDuplicatesForOrigins }}</p>
          <table class="table"
                 v-else="">
            <caption>
              {{ T.wordsActivityReportDuplicatesForOriginsDescription }}
            </caption>
            <thead>
              <tr>
                <th>{{ T.wordsActivityReportOrigin }}</th>
                <th>{{ T.profileUsername }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="origin in origins">
                <td>{{ origin.origin }}</td>
                <td><span v-for="user in origin.usernames"><a v-bind:href=
                "`/profile/${user.username}`"><strong><omegaup-user-username v-bind:classname=
                "user.classname"
                                       v-bind:username=
                                       "user.username"></omegaup-user-username></strong></a>&nbsp;</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';
import user_Username from '../user/Username.vue';
export default {
  props: {type: String, alias: String, report: Object},
  data: function() {
    return { T: T, UI: UI }
  },
  methods: {
    addMapping: function(mapping, key, value) {
      if (key in mapping) {
        mapping[key].push(value);
      } else {
        mapping[key] = [value];
      }
    },
    getClassByUser: function(events) {
      let obj = {};
      for (let evt of events) {
        obj[evt.username] = evt.classname;
      }
      return obj;
    },
  },
  computed: {
    wordsReportSummary: function() {
      let self = this;
      return self.type == 'contest' ? T.wordsActivityReportSummaryContest :
                                      T.wordsActivityReportSummaryCourse
    },
    events: function() {
      let self = this;
      let events = self.report.events;
      return events;
    },
    users: function() {
      let self = this;
      let events = self.report.events;
      let classByUser = self.getClassByUser(events);
      let userMapping = {};
      for (let evt of events) {
        self.addMapping(userMapping, evt.username, evt.ip);
        evt.ip = '' + evt.ip.toString();
        evt.time = evt.time.toLocaleString();
      }
      let users = [];
      let sortedUsers = Object.keys(userMapping);
      sortedUsers.sort();
      for (let user of sortedUsers) {
        let ips = Array.from(new Set(userMapping[user]));
        if (ips.length == 1) continue;
        ips.sort();
        users.push({username: user, classname: classByUser[user], ips: ips});
      }
      return users;
    },
    origins: function() {
      let self = this;
      let events = self.report.events;
      let classByUser = self.getClassByUser(events);
      let originMapping = {};
      for (let evt of events) {
        self.addMapping(originMapping, evt.ip, evt.username);
        evt.ip = '' + evt.ip.toString();
      }
      let origins = [];
      let sortedOrigins = Object.keys(originMapping);
      sortedOrigins.sort();
      for (let origin of sortedOrigins) {
        let users = Array.from(new Set(originMapping[origin]));
        if (users.length == 1) continue;
        users.sort();
        origins.push({
          origin: origin,
          usernames: users.map(
              u => {return {username: u, classname: classByUser[u]}})
        });
      }
      return origins;
    }
  },
  components: {
    'omegaup-user-username': user_Username,
  }
}
</script>
