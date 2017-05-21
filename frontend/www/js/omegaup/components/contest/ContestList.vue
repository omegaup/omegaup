<template>
<div class="panel panel-default no-bottom-margin">

  <div class="panel-heading">
    <h3 class="panel-title">{{ T.wordsContests }}</h3>
  </div>

  <div class="panel-body">
    <div class="checkbox btn-group">
      <label>
        <input type="checkbox" class="show-admin-contests" v-on:click="onShowAdmin" />
        {{ T.contestListShowAdminContests }}
      </label>
    </div>
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        {{ T.forSelectedItems }}<span class="caret"></span>
      </button>
      <ul class="dropdown-menu" role="menu">
        <li><a v-on:click="onBulkUpdate(true)">{{ T.makePublic }}</a></li>
        <li><a v-on:click="onBulkUpdate(false)">{{ T.makePrivate }}</a></li>
        <li class="divider"></li>
      </ul>
    </div>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th></th>
        <th>{{ T.wordsTitle }}</th>
        <th>{{ T.arenaPracticeStartTime }}</th>
        <th>{{ T.arenaPracticeEndtime }}</th>
        <th>{{ T.contestsTablePublic }}</th>
        <th colspan="2">Scoreboard</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="contest in contests">
        <td><input type='checkbox' v-bind:id="contest.alias"></input></td>
        <td><strong><a v-bind:href="'/arena/' + contest.alias + '/'">
            {{ contest.title }}
        </a></strong></td>
        <td><a v-bind:href="makeWorldClockLink(contest.start_time)">
            {{ contest.start_time.format('long') }}
        </a></td>
        <td><a v-bind:href="makeWorldClockLink(contest.finish_time)">
            {{ contest.finish_time.format('long') }}
        </a></td>

        <td v-if="contest.public == '1'">{{ T.wordsYes }}</td>
        <td v-else>{{ T.wordsNo }}</td>

        <td v-if="contest.scoreboard_url">
          <a class="glyphicon glyphicon-link"
              v-bind:href="'/arena/' + contest.alias + '/scoreboard/' + contest.scoreboard_url"
              v-bind:title="T.contestScoreboardLink">
              Public
          </a>
        </td>

        <td v-if="contest.scoreboard_url_admin">
          <a class="glyphicon glyphicon-link"
             v-bind:href="'/arena/' + contest.alias + '/scoreboard/' + contest.scoreboard_url_admin"
             v-bind:title="T.contestScoreboardAdminLink">
            Admin
          </a>
        </td>

        <td>
          <a class="glyphicon glyphicon-edit"
             v-bind:href="'/contest/' + contest.alias + '/edit/'"
             v-bind:title="T.wordsEdit"></a>
        </td>
        <td>
          <a class="glyphicon glyphicon-dashboard"
             v-bind:href="'/arena/' + contest.alias + '/admin/'"
             v-bind:title="T.contestListSubmissions"></a>
        </td>
        <td>
          <a class="glyphicon glyphicon-stats"
             v-bind:href="'/contest/' + contest.alias + '/stats/'"
             v-bind:title="T.profileStatistics"></a>
        </td>
        <td>
          <a class="glyphicon glyphicon-time"
             v-bind:href="'/contest/' + contest.alias + '/activity/'"
             v-bind:title="T.contestActivityReport"></a>
        </td>
        <td>
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
  props: {
    contests: Array,
  },
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
      return 'https://timeanddate.com/worldclock/fixedtime.html?iso=' + date.toISOString();
    },
    onBulkUpdate: function(publiclyVisible) {
      this.$emit('bulk-update', publiclyVisible);
    },
    onShowAdmin: function() {
      this.$emit('toggle-show-admin', this.$el.querySelector('.show-admin-contests').checked);
    },
  },
};
</script>
