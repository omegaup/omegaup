<template>
  <div class="panel panel-default no-bottom-margin">
    <div class="panel-heading">
      <h3 class="panel-title">{{ title }}</h3>
    </div>
    <div class="panel-body"
         v-if="isAdmin">
      <div class="checkbox btn-group"
           v-if="isAdmin">
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
            <a v-on:click="onBulkUpdate('public')">{{ T.makePublic }}</a>
          </li>
          <li>
            <a v-on:click="onBulkUpdate('private')">{{ T.makePrivate }}</a>
          </li>
          <li>
            <a v-on:click="onBulkUpdate('registration')">{{ T.makeRegistration }}</a>
          </li>
          <li class="divider"></li>
        </ul>
      </div>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th v-if="isAdmin"></th>
          <th>{{ T.wordsTitle }}</th>
          <th>{{ T.arenaPracticeStartTime }}</th>
          <th>{{ T.arenaPracticeEndtime }}</th>
          <th v-if="isAdmin">{{ T.contestNewFormAdmissionMode }}</th>
          <th colspan="2"
              v-if="isAdmin">{{ T.wordsScoreboard }}</th>
          <th colspan="7"
              v-if="isAdmin"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="contest in contests">
          <td v-if="isAdmin"><input type='checkbox'
                 v-bind:id="contest.alias"></td>
          <td><strong><a v-bind:href="'/arena/' + contest.alias + '/'">{{ UI.contestTitle(contest)
          }}</a></strong></td>
          <td>
            <a v-bind:href="makeWorldClockLink(contest.start_time)">{{
            contest.start_time.format('long') }}</a>
          </td>
          <td>
            <a v-bind:href="makeWorldClockLink(contest.finish_time)">{{
            contest.finish_time.format('long') }}</a>
          </td>
          <td v-if="!isAdmin"></td>
          <td v-else-if="contest.admission_mode == 'public'">{{ T.wordsPublic }}</td>
          <td v-else-if="contest.admission_mode == 'private'">{{ T.wordsPrivate }}</td>
          <td v-else-if="contest.admission_mode == 'registration'">{{ T.wordsRegistration }}</td>
          <td v-else=""></td>
          <td v-if="isAdmin">
            <a class="glyphicon glyphicon-link"
                v-bind:href="'/arena/' + contest.alias + '/scoreboard/' + contest.scoreboard_url"
                v-bind:title="T.contestScoreboardLink"
                v-if="contest.scoreboard_url">Public</a>
          </td>
          <td v-if="isAdmin">
            <a class="glyphicon glyphicon-link"
                v-bind:href=
                "'/arena/' + contest.alias + '/scoreboard/' + contest.scoreboard_url_admin"
                v-bind:title="T.contestScoreboardAdminLink"
                v-if="contest.scoreboard_url_admin">Admin</a>
          </td>
          <td v-if="isAdmin">
            <a class="glyphicon glyphicon-edit"
                v-bind:href="'/contest/' + contest.alias + '/edit/'"
                v-bind:title="T.wordsEdit"></a>
          </td>
          <td v-if="isAdmin">
            <a class="glyphicon glyphicon-dashboard"
                v-bind:href="'/arena/' + contest.alias + '/admin/'"
                v-bind:title="T.contestListSubmissions"></a>
          </td>
          <td v-if="isAdmin">
            <a class="glyphicon glyphicon-stats"
                v-bind:href="'/contest/' + contest.alias + '/stats/'"
                v-bind:title="T.profileStatistics"></a>
          </td>
          <td v-if="isAdmin">
            <a class="glyphicon glyphicon-time"
                v-bind:href="'/contest/' + contest.alias + '/activity/'"
                v-bind:title="T.wordsActivityReport"></a>
          </td>
          <td v-if="isAdmin">
            <a class="glyphicon glyphicon-print"
                v-bind:href="'/arena/' + contest.alias + '/print/'"
                v-bind:title="T.contestPrintableVersion"></a>
          </td>
          <td v-if="isAdmin">
            <a class="glyphicon glyphicon-download"
                href="#download"
                v-bind:title="T.contestDownloadListOfUsersInContest"
                v-on:click="onDownloadCsv(contest.alias)"></a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

@Component({})
export default class List extends Vue {
  @Prop() contests!: omegaup.Contest[];
  @Prop() isAdmin!: boolean;
  @Prop() title!: string;

  T = T;
  UI = UI;

  makeWorldClockLink(date: Date) : string {
    if (!date) {
      return '#';
    }
    return 'https://timeanddate.com/worldclock/fixedtime.html?iso=' +
           date.toISOString();
  }

  @Emit('bulk-update')
  onBulkUpdate(admissionMode: string) : string {
    return admissionMode;
  }

  @Emit('toggle-show-admin')
  onShowAdmin() : boolean {
    const input = this.$el.querySelector('.show-admin-contests') as HTMLInputElement;
    return input.checked;
  }

  @Emit('download-csv-users')
  onDownloadCsv(contestAlias: string) : string {
    return contestAlias;
  }
}

</script>
