<template>
  <div class="panel panel-default no-bottom-margin">
    <div class="panel-heading">
      <h3 class="panel-title"
          v-if="isParticipant">{{ T.contestMyActiveContests }}</h3>
      <h3 class="panel-title"
          v-else="">{{ T.wordsContests }}</h3>
    </div>
    <div class="panel-body"
         v-if="!isParticipant">
      <div class="checkbox btn-group"
           v-if="!isParticipant">
        <label><input class="show-admin-contests"
               type="checkbox"
               v-on:click="onShowAdmin"> {{ T.contestListShowAdminContests }}</label>
      </div>
      <div class="btn-group">
        <button class="btn btn-default dropdown-toggle"
             data-toggle="dropdown"
             type="button">{{ T.forSelectedItems }}<span class="caret"></span></button>
        <ul class="dropdown-menu"
            role="menu">
          <li>
            <a v-on:click="onBulkUpdate(true)">{{ T.makePublic }}</a>
          </li>
          <li>
            <a v-on:click="onBulkUpdate(false)">{{ T.makePrivate }}</a>
          </li>
          <li class="divider"></li>
        </ul>
      </div>
    </div>
    <template v-if="!isParticipant"></template>
    <table class="table">
      <thead v-if="!isIndex">
        <tr>
          <th v-if="!isParticipant"></th>
          <th>{{ T.wordsTitle }}</th>
          <th>{{ T.arenaPracticeStartTime }}</th>
          <th>{{ T.arenaPracticeEndtime }}</th>
          <th v-if="!isParticipant">{{ T.contestsTablePublic }}</th>
          <th colspan="2"
              v-if="!isParticipant">Scoreboard</th>
          <th v-if="!isParticipant"></th>
          <th v-if="!isParticipant"></th>
          <th v-if="!isParticipant"></th>
          <th v-if="!isParticipant"></th>
          <th v-if="!isParticipant"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="contest in contests">
          <td v-if="!isParticipant"><input type='checkbox'
                 v-bind:id="contest.alias"></td>
          <td><strong><a v-bind:href="'/arena/' + contest.alias + '/'">{{ contest.title
          }}</a></strong></td>
          <td>
            <a v-bind:href="makeWorldClockLink(contest.start_time)"
                v-if="!isIndex">{{ contest.start_time.format('long') }}</a>
          </td>
          <td>
            <a v-bind:href="makeWorldClockLink(contest.finish_time)"
                v-if="!isIndex">{{ contest.finish_time.format('long') }}</a>
          </td>
          <td v-if="contest.public == '1'">{{ T.wordsYes }}</td>
          <td v-else="">{{ T.wordsNo }}</td>
          <td v-if="contest.scoreboard_url &amp;&amp; !isParticipant">
            <a class="glyphicon glyphicon-link"
                v-bind:href="'/arena/' + contest.alias + '/scoreboard/' + contest.scoreboard_url"
                v-bind:title="T.contestScoreboardLink">Public</a>
          </td>
          <td v-if="contest.scoreboard_url_admin &amp;&amp; !isParticipant">
            <a class="glyphicon glyphicon-link"
                v-bind:href=
                "'/arena/' + contest.alias + '/scoreboard/' + contest.scoreboard_url_admin"
                v-bind:title="T.contestScoreboardAdminLink">Admin</a>
          </td>
          <td v-if="!isParticipant">
            <a class="glyphicon glyphicon-edit"
                v-bind:href="'/contest/' + contest.alias + '/edit/'"
                v-bind:title="T.wordsEdit"></a>
          </td>
          <td v-if="!isParticipant">
            <a class="glyphicon glyphicon-dashboard"
                v-bind:href="'/arena/' + contest.alias + '/admin/'"
                v-bind:title="T.contestListSubmissions"></a>
          </td>
          <td v-if="!isParticipant">
            <a class="glyphicon glyphicon-stats"
                v-bind:href="'/contest/' + contest.alias + '/stats/'"
                v-bind:title="T.profileStatistics"></a>
          </td>
          <td v-if="!isParticipant">
            <a class="glyphicon glyphicon-time"
                v-bind:href="'/contest/' + contest.alias + '/activity/'"
                v-bind:title="T.contestActivityReport"></a>
          </td>
          <td v-if="!isParticipant">
            <a class="glyphicon glyphicon-print"
                v-bind:href="'/arena/' + contest.alias + '/print/'"
                v-bind:title="T.contestPrintableVersion"></a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
export default {
  props: {contests: Array, isParticipant: Boolean, isIndex: Boolean},
  data: function() {
    return {
      T: T,
    };
  },
  methods: {
    makeWorldClockLink: function(date) {
      if (!date) {
        return '#';
      }
      return 'https://timeanddate.com/worldclock/fixedtime.html?iso=' +
             date.toISOString();
    },
    onBulkUpdate: function(publiclyVisible) {
      this.$emit('bulk-update', publiclyVisible);
    },
    onShowAdmin: function() {
      this.$emit('toggle-show-admin',
                 this.$el.querySelector('.show-admin-contests').checked);
    },
  },
};
</script>
