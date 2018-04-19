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
                  <a v-bind:href="event.profile_url">{{ event.username }}</a>
                </td>
                <td>{{&nbsp;event.time }}</td>
                <td>{{&nbsp;event.ip }}</td>
                <td>{{&nbsp;event.name }}</td>
                <td>
                  <a v-bind:href="event.event.problem_url">{{&nbsp;event.event.problem }}</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="tab-pane users"
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
                  <a v-bind:href="user.profile_url">{{ user.username }}</a>
                </td>
                <td>{{ user.ips }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="tab-pane origins"
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
                <td><span v-for="username in origin.usernames"><a v-bind:href=
                "username.profile_url">{{ username.username }}</a>&nbsp;</span></td>
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
export default {
  props: {
    type: String,
    alias: String,
    events: Array,
    users: Array,
    origins: Array,
  },
  data: function() {
    return { T: T, }
  }
}
</script>
