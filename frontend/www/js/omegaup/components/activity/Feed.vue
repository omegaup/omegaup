<template>
  <div class="post">
    <div class="copy">
      <h1><a v-bind:href="`/${type}/${alias}/`">{{ alias }}</a> — {{ T.contestActivityReport
      }}</h1>
      <p>{{ T.contestActivityReportSummary }}</p><!-- Nav tabs -->
      <ul class="nav nav-tabs"
          role="tablist">
        <li class="active"
            role="presentation">
          <a aria-controls="report"
              data-toggle="tab"
              href=".report"
              role="tab">{{ T.contestActivityReportReport }}</a>
        </li>
        <li role="presentation">
          <a aria-controls="users"
              data-toggle="tab"
              href=".users"
              role="tab">{{ T.contestActivityReportUsers }}</a>
        </li>
        <li role="presentation">
          <a aria-controls="origins"
              data-toggle="tab"
              href=".origins"
              role="tab">{{ T.contestActivityReportOrigins }}</a>
        </li>
      </ul><!-- Tab panes -->
      <div class="tab-content">
        <div class="tab-pane active report"
             name="report"
             role="tabpanel">
          <table class="table">
            <thead>
              <tr>
                <th>{{ T.profileUsername }}</th>
                <th>{{ T.wordsTime }}</th>
                <th>{{ T.contestActivityReportOrigin }}</th>
                <th colspan="2">{{ T.contestActivityReportEvent }}</th>
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
                <td>{{ event.name }}</td>
                <td><span v-if="event.event.problem"><a v-bind:href=
                "`/arena/problem/${event.event.problem}/`">{{ event.event.problem
                }}</a></span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="tab-pane users"
             name="users"
             role="tabpanel">
          <p v-if="users &lt;= 0">{{ T.contestActivityReportNoDuplicatesForUsers }}</p>
          <table class="table"
                 v-else="">
            <caption>
              {{ T.contestActivityReportDuplicatesForUsersDescription }}
            </caption>
            <thead>
              <tr>
                <th>{{ T.profileUsername }}</th>
                <th>{{ T.contestActivityReportOrigin }}</th>
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
        </div>
        <div class="tab-pane origins"
             name="origins"
             role="tabpanel">
          <p v-if="origins &lt;= 0">{{ T.contestActivityReportNoDuplicatesForOrigins }}</p>
          <table class="table"
                 v-else="">
            <caption>
              {{ T.contestActivityReportDuplicatesForOriginsDescription }}
            </caption>
            <thead>
              <tr>
                <th>{{ T.contestActivityReportOrigin }}</th>
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
import user_Username from '../user/Username.vue';
export default {
  props: {
    type: String,
    alias: String,
    events: Array,
    users: Array,
    origins: Array,
    report: Object
  },
  data: function() {
    return { T: T, }
  },
  mounted: function() {
    let self = this;
    self.getActivityReport();
  },
  methods: {
    addMapping: function(mapping, key, value) {
      if (!mapping.hasOwnProperty(key)) {
        mapping[key] = {};
      }
      if (!mapping[key].hasOwnProperty(value)) {
        mapping[key][value] = true;
      }
    },
    getUsersByClass: function(events) {
      let users = [];
      for (let evt of events) {
        users.push({username: evt.username, classname: evt.classname});
      }
      let hash = {};
      users = users.filter(function(current) {
        let exists = !hash[current.username] || false;
        hash[current.username] = true;
        return exists;
      });
      let obj = {};
      for (let index in users) {
        obj[users[index]['username']] = users[index]['classname'];
      }
      return obj;
    },
    getActivityReport: function() {
      let self = this;
      self.formattedReport;
    },
  },
  computed: {
    formattedReport: function() {
      let self = this;
      let events = self.report.events;
      let usersByClass = self.getUsersByClass(events);
      let userMapping = {};
      let originMapping = {};
      for (let evt of events) {
        self.addMapping(originMapping, evt.ip, evt.username);
        self.addMapping(userMapping, evt.username, evt.ip);
        evt.ip = '' + evt.ip;
        evt.time = Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', evt.time);
      }

      let users = [];
      let sortedUsers = Object.keys(userMapping);
      sortedUsers.sort();
      for (let srtU of sortedUsers) {
        let ips = Object.keys(userMapping[srtU]);
        if (ips.length == 1) continue;
        ips.sort();
        users.push({username: srtU, classname: usersByClass[srtU], ips: ips});
      }

      let origins = [];
      let sortedOrigins = Object.keys(originMapping);
      sortedOrigins.sort();
      for (let srtO of sortedOrigins) {
        let users = Object.keys(originMapping[srtO]);
        if (users.length == 1) continue;
        users.sort();
        for (let j = 0; j < users.length; j++) {
          users[j] = {username: users[j], classname: usersByClass[users[j]]};
        }
        origins.push({origin: srtO, usernames: users});
      }
      self.events = events;
      self.users = users;
      self.origins = origins;
    }
  },
  components: {
    'omegaup-user-username': user_Username,
  }
}
</script>
