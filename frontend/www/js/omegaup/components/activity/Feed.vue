<template>
  <div class="post">
    <div class="copy">
      <h1>
        <a v-bind:href="`/${type}/${alias}/`">{{ alias }}</a> —
        {{ T.wordsActivityReport }}
      </h1>
      <p>{{ wordsReportSummary }}</p>
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
          <a
            class="nav-link"
            data-toggle="tab"
            href="#report"
            role="tab"
            aria-controls="report"
            aria-selected="true"
            v-on:click="showTab = 'report'"
            v-bind:class="{ active: showTab === 'report' }"
            >{{ T.wordsActivityReportReport }}</a
          >
        </li>
        <li class="nav-item" role="presentation">
          <a
            class="nav-link"
            data-toggle="tab"
            href="#users"
            role="tab"
            aria-controls="users"
            aria-selected="false"
            v-on:click="showTab = 'users'"
            v-bind:class="{ active: showTab === 'users' }"
            >{{ T.wordsActivityReportUsers }}</a
          >
        </li>
        <li class="nav-item" role="presentation">
          <a
            class="nav-link"
            data-toggle="tab"
            href="#origins"
            role="tab"
            aria-controls="origins"
            aria-selected="false"
            v-on:click="showTab = 'origins'"
            v-bind:class="{ active: showTab === 'origins' }"
            >{{ T.wordsActivityReportOrigins }}</a
          >
        </li>
      </ul>
      <!-- Tab panes -->
      <div class="tab-content mt-2">
        <div
          class="tab-pane"
          role="tabpanel"
          aria-labelledby="report-tab"
          v-bind:class="{
            show: showTab === 'report',
            active: showTab === 'report',
          }"
          v-show="showTab === 'report'"
        >
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
                  <omegaup-user-username
                    v-bind:classname="event.classname"
                    v-bind:username="event.username"
                    v-bind:linkify="true"
                  ></omegaup-user-username>
                </td>
                <td>{{ time.formatDateTime(event.time) }}</td>
                <td>{{ event.ip.toString() }}</td>
                <td>{{ event.event.name }}</td>
                <td>
                  <span v-if="event.event.problem">
                    <a v-bind:href="`/arena/problem/${event.event.problem}/`">{{
                      event.event.problem
                    }}</a>
                  </span>
                  <span v-if="event.event.courseAlias" class="mr-2">
                    <a v-bind:href="`/course/${event.event.courseAlias}/`">{{
                      event.event.courseName
                    }}</a>
                  </span>
                  <span v-if="event.event.result">{{
                    event.event.result
                  }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div
          class="tab-pane"
          role="tabpanel"
          aria-labelledby="users-tab"
          v-bind:class="{
            show: showTab === 'users',
            active: showTab === 'users',
          }"
          v-show="showTab === 'users'"
        >
          <p v-if="users.length &lt;= 0">
            {{ T.wordsActivityReportNoDuplicatesForUsers }}
          </p>
          <table class="table" v-else>
            <caption>
              {{
                T.wordsActivityReportDuplicatesForUsersDescription
              }}
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
                  <omegaup-user-username
                    v-bind:linkify="true"
                    v-bind:classname="user.classname"
                    v-bind:username="user.username"
                  ></omegaup-user-username>
                </td>
                <td>
                  <span v-for="ip in user.ips" class="mx-1">{{ ip }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div
          class="tab-pane"
          role="tabpanel"
          aria-labelledby="origins-tab"
          v-bind:class="{
            show: showTab === 'origins',
            active: showTab === 'origins',
          }"
          v-show="showTab === 'origins'"
        >
          <p v-if="origins.length &lt;= 0">
            {{ T.wordsActivityReportNoDuplicatesForOrigins }}
          </p>
          <table class="table" v-else>
            <caption>
              {{
                T.wordsActivityReportDuplicatesForOriginsDescription
              }}
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
                <td>
                  <span v-for="user in origin.usernames" class="mx-1">
                    <omegaup-user-username
                      v-bind:linkify="true"
                      v-bind:classname="user.classname"
                      v-bind:username="user.username"
                    ></omegaup-user-username
                  ></span>
                </td>
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
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
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

const availableTabs = ['report', 'users', 'origins'];

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class ActivityFeed extends Vue {
  @Prop() type!: string;
  @Prop() alias!: string;
  @Prop() report!: types.ActivityEvent[];

  T = T;
  time = time;
  showTab = 'report';

  addMapping(mapping: Mapping, key: string, value: string): void {
    if (key in mapping) {
      mapping[key].push(value);
    } else {
      mapping[key] = [value];
    }
  }

  get wordsReportSummary(): string {
    return this.type == 'contest'
      ? T.wordsActivityReportSummaryContest
      : T.wordsActivityReportSummaryCourse;
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
      self.addMapping(userMapping, evt.username, String(evt.ip));
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
      self.addMapping(originMapping, String(evt.ip), evt.username);
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
        usernames: users.map((u) => {
          return { username: u, classname: self.classByUser[u] };
        }),
      });
    }
    return origins;
  }
}
</script>
