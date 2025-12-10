<template>
  <div class="post">
    <div class="copy">
      <h1>
        <a :href="typeUrl">{{ alias }}</a> — {{ T.activityReport }}
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
            :class="{ active: showTab === 'report' }"
            @click="showTab = 'report'"
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
            :class="{ active: showTab === 'users' }"
            @click="showTab = 'users'"
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
            :class="{ active: showTab === 'origins' }"
            @click="showTab = 'origins'"
            >{{ T.activityReportOrigins }}</a
          >
        </li>
      </ul>
      <!-- Tab panes -->
      <div class="tab-content mt-2">
        <div
          v-show="showTab === 'report'"
          class="tab-pane"
          role="tabpanel"
          aria-labelledby="report-tab"
          :class="{
            active: showTab === 'report',
          }"
        >
          <div class="card">
            <table class="table mb-0">
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
                      :classname="event.classname"
                      :username="event.username"
                      :linkify="true"
                    ></omegaup-user-username>
                  </td>
                  <td>{{ time.formatDateTime(event.time) }}</td>
                  <td>{{ event.ip.toString() }}</td>
                  <td>{{ event.event.name }}</td>
                  <td>
                    <span v-if="event.event.problem">
                      <a :href="`/arena/problem/${event.event.problem}/`">{{
                        event.event.problem
                      }}</a>
                    </span>
                    <span v-if="event.event.courseAlias" class="mr-2">
                      <a :href="`/course/${event.event.courseAlias}/`">{{
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
            <div class="card-footer">
              <omegaup-common-paginator
                :pager-items="pagerItems"
              ></omegaup-common-paginator>
            </div>
          </div>
        </div>
        <div
          v-show="showTab === 'users'"
          class="tab-pane"
          role="tabpanel"
          aria-labelledby="users-tab"
          :class="{
            active: showTab === 'users',
          }"
        >
          <p v-if="users.length &lt;= 0">
            {{ T.activityReportNoDuplicatesForUsers }}
          </p>
          <table v-else class="table">
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
                    :linkify="true"
                    :classname="user.classname"
                    :username="user.username"
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
          v-show="showTab === 'origins'"
          class="tab-pane"
          role="tabpanel"
          aria-labelledby="origins-tab"
          :class="{
            active: showTab === 'origins',
          }"
        >
          <p v-if="origins.length &lt;= 0">
            {{ T.activityReportNoDuplicatesForOrigins }}
          </p>
          <table v-else class="table">
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
                      :linkify="true"
                      :classname="user.classname"
                      :username="user.username"
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
import common_Paginator from '../common/Paginator.vue';

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
    'omegaup-common-paginator': common_Paginator,
  },
})
export default class ActivityFeed extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() type!: string;
  @Prop() alias!: string;
  @Prop() report!: types.ActivityEvent[];
  @Prop() pagerItems!: types.PageItem[];

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

  get typeUrl(): string {
    if (this.type == 'contest') {
      return `/arena/${this.alias}/`;
    }
    return `/${this.type}/${this.alias}/`;
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
