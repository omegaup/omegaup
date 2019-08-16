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
              <tr v-for="event in report">
                <td>
                  <a v-bind:href=
                  "`/profile/${event.username}`"><strong><omegaup-user-username v-bind:classname=
                  "event.classname"
                                         v-bind:username=
                                         "event.username"></omegaup-user-username></strong></a>
                </td>
                <td>{{ UI.formatDateTime(event.time) }}</td>
                <td>{{ event.ip.toString() }}</td>
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
          <p v-if="users.length &lt;= 0">{{ T.wordsActivityReportNoDuplicatesForUsers }}</p>
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
          <p v-if="origins.length &lt;= 0">{{ T.wordsActivityReportNoDuplicatesForOrigins }}</p>
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

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';
import user_Username from '../user/Username.vue';

interface Mapping {
  [key: string]: string[];
}

interface User {
  username: string;
  classname: string;
  ips: string[];
}

interface Origin {
  origin: string;
  usernames: {
    username: string;
    classname: string;
  }[];
}

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class ActivityFeed extends Vue {
  @Prop() type!: string;
  @Prop() alias!: string;
  @Prop() report!: omegaup.Report[];

  T = T;
  UI = UI;

  addMapping(mapping: any, key: string, value: string): void {
    if (key in mapping) {
      mapping[key].push(value);
    } else {
      mapping[key] = [value];
    }
  }

  get wordsReportSummary(): string {
    return this.type == 'contest'
      ? this.T.wordsActivityReportSummaryContest
      : this.T.wordsActivityReportSummaryCourse;
  }

  get classByUser(): { [key: string]: string } {
    let events = this.report;
    let obj: { [key: string]: string } = {};
    for (let evt of events) {
      obj[evt.username] = evt.classname;
    }
    return obj;
  }

  get users(): User[] {
    let self = this;
    let userMapping: Mapping = {};
    for (let evt of this.report) {
      self.addMapping(userMapping, evt.username, evt.ip);
    }
    let users: User[] = [];
    let sortedUsers = Object.keys(userMapping);
    sortedUsers.sort();
    for (let user of sortedUsers) {
      let ips: string[] = Array.from(new Set(userMapping[user]));
      if (ips.length == 1) continue;
      ips.sort();
      users.push({
        username: user,
        classname: self.classByUser[user],
        ips: ips,
      });
    }
    return users;
  }

  get origins(): Origin[] {
    let self = this;
    let originMapping: Mapping = {};
    for (let evt of this.report) {
      self.addMapping(originMapping, evt.ip, evt.username);
    }
    let origins: Origin[] = [];
    let sortedOrigins = Object.keys(originMapping);
    sortedOrigins.sort();
    for (let origin of sortedOrigins) {
      let users: string[] = Array.from(new Set(originMapping[origin]));
      if (users.length == 1) continue;
      users.sort();
      origins.push({
        origin: origin,
        usernames: users.map(u => {
          return { username: u, classname: self.classByUser[u] };
        }),
      });
    }
    return origins;
  }
}

</script>
