<template>
  <div class="post">
    <div class="copy">
      <h1>
        <a v-bind:href="`/${type}/${alias}/`">{{ alias }}</a> —
        {{ T.activityReport }}
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
            >{{ T.activityReportReport }}</a
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
            >{{ T.activityReportUsers }}</a
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
            >{{ T.activityReportOrigins }}</a
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
            active: showTab === 'report',
          }"
          v-show="showTab === 'report'"
        >
          <table class="table">
            <thead>
              <tr>
                <th>{{ T.profileUsername }}</th>
                <th>{{ T.wordsTime }}</th>
                <th>{{ T.activityReportOrigin }}</th>
                <th colspan="2">{{ T.activityReportEvent }}</th>
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
                  <span v-if="event.event.cloneResult">{{
                    event.event.cloneResult
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
            active: showTab === 'users',
          }"
          v-show="showTab === 'users'"
        >
          <p v-if="users.length &lt;= 0">
            {{ T.activityReportNoDuplicatesForUsers }}
          </p>
          <table class="table" v-else>
            <caption>
              {{
                T.activityReportDuplicatesForUsersDescription
              }}
            </caption>
            <thead>
              <tr>
                <th>{{ T.profileUsername }}</th>
                <th>{{ T.activityReportOrigin }}</th>
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
            active: showTab === 'origins',
          }"
          v-show="showTab === 'origins'"
        >
          <p v-if="origins.length &lt;= 0">
            {{ T.activityReportNoDuplicatesForOrigins }}
          </p>
          <table class="table" v-else>
            <caption>
              {{
                T.activityReportDuplicatesForOriginsDescription
              }}
            </caption>
            <thead>
              <tr>
                <th>{{ T.activityReportOrigin }}</th>
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
      ? T.activityReportSummaryContest
      : T.activityReportSummaryCourse;
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
    let userMapping: Mapping = {};
    for (let evt of this.report) {
      this.addMapping(userMapping, evt.username, String(evt.ip));
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
        classname: this.classByUser[user],
        ips: ips,
      });
    }
    return users;
  }

  get origins(): Origin[] {
    let originMapping: Mapping = {};
    for (let evt of this.report) {
      this.addMapping(originMapping, String(evt.ip), evt.username);
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
          return { username: u, classname: this.classByUser[u] };
        }),
      });
    }
    return origins;
  }
}
</script>
