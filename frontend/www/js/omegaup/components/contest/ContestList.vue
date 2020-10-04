<template>
  <div class="panel panel-default no-bottom-margin">
    <div class="panel-heading">
      <h3 class="panel-title">{{ title }}</h3>
    </div>
    <div v-if="isAdmin" class="panel-body">
      <div v-if="isAdmin" class="checkbox btn-group">
        <label
          ><input
            class="show-admin-contests"
            type="checkbox"
            @click="onShowAdmin"
          />
          {{ T.contestListShowAdminContests }}</label
        >
      </div>
      <div class="btn-group">
        <button
          class="btn btn-default dropdown-toggle"
          data-toggle="dropdown"
          type="button"
        >
          {{ T.forSelectedItems }}<span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li>
            <a @click="onBulkUpdate(selectedContests, 'public')">{{
              T.makePublic
            }}</a>
          </li>
          <li>
            <a @click="onBulkUpdate(selectedContests, 'private')">{{
              T.makePrivate
            }}</a>
          </li>
          <li>
            <a @click="onBulkUpdate(selectedContests, 'registration')">{{
              T.makeRegistration
            }}</a>
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
          <th v-if="isAdmin" colspan="2">{{ T.wordsScoreboard }}</th>
          <th v-if="isAdmin" colspan="7"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="contest in contests">
          <td v-if="isAdmin">
            <input
              :id="contest.alias"
              v-model="selectedContests"
              type="checkbox"
              :value="contest.alias"
            />
          </td>

          <td>
            <strong
              ><a :href="'/arena/' + contest.alias + '/'">{{
                ui.contestTitle(contest)
              }}</a></strong
            >
          </td>
          <td>
            <a :href="makeWorldClockLink(contest.start_time)">{{
              contest.start_time.format('long')
            }}</a>
          </td>
          <td>
            <a :href="makeWorldClockLink(contest.finish_time)">{{
              contest.finish_time.format('long')
            }}</a>
          </td>
          <td v-if="!isAdmin"></td>
          <td v-else-if="contest.admission_mode == 'public'">
            {{ T.wordsPublic }}
          </td>
          <td v-else-if="contest.admission_mode == 'private'">
            {{ T.wordsPrivate }}
          </td>
          <td v-else-if="contest.admission_mode == 'registration'">
            {{ T.wordsRegistration }}
          </td>
          <td v-else></td>
          <td v-if="isAdmin">
            <a
              v-if="contest.scoreboard_url"
              class="glyphicon glyphicon-link"
              :href="
                '/arena/' +
                contest.alias +
                '/scoreboard/' +
                contest.scoreboard_url
              "
              :title="T.contestScoreboardLink"
              >Public</a
            >
          </td>
          <td v-if="isAdmin">
            <a
              v-if="contest.scoreboard_url_admin"
              class="glyphicon glyphicon-link"
              :href="
                '/arena/' +
                contest.alias +
                '/scoreboard/' +
                contest.scoreboard_url_admin
              "
              :title="T.contestScoreboardAdminLink"
              >Admin</a
            >
          </td>
          <td v-if="isAdmin">
            <a
              class="glyphicon glyphicon-edit"
              :href="'/contest/' + contest.alias + '/edit/'"
              :title="T.wordsEdit"
            ></a>
          </td>
          <td v-if="isAdmin">
            <a
              class="glyphicon glyphicon-dashboard"
              :href="'/arena/' + contest.alias + '/admin/'"
              :title="T.contestListSubmissions"
            ></a>
          </td>
          <td v-if="isAdmin">
            <a
              class="glyphicon glyphicon-stats"
              :href="'/contest/' + contest.alias + '/stats/'"
              :title="T.profileStatistics"
            ></a>
          </td>
          <td v-if="isAdmin">
            <a
              class="glyphicon glyphicon-time"
              :href="'/contest/' + contest.alias + '/activity/'"
              :title="T.activityReport"
            ></a>
          </td>
          <td v-if="isAdmin">
            <a
              class="glyphicon glyphicon-print"
              :href="'/arena/' + contest.alias + '/print/'"
              :title="T.contestPrintableVersion"
            ></a>
          </td>
          <td v-if="isAdmin">
            <a
              class="glyphicon glyphicon-download"
              href="#download"
              :title="T.contestDownloadListOfUsersInContest"
              @click="onDownloadCsv(contest.alias)"
            ></a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class List extends Vue {
  @Prop() contests!: omegaup.Contest[];
  @Prop() isAdmin!: boolean;
  @Prop() title!: string;

  T = T;
  ui = ui;
  selectedContests = [];

  makeWorldClockLink(date: Date): string {
    if (!date) {
      return '#';
    }
    return (
      'https://timeanddate.com/worldclock/fixedtime.html?iso=' +
      date.toISOString()
    );
  }

  @Emit('bulk-update')
  onBulkUpdate(admissionMode: string): string {
    this.selectedContests = [];
    return admissionMode;
  }

  @Emit('toggle-show-admin')
  onShowAdmin(): boolean {
    const input = this.$el.querySelector(
      '.show-admin-contests',
    ) as HTMLInputElement;
    return input.checked;
  }

  @Emit('download-csv-users')
  onDownloadCsv(contestAlias: string): string {
    return contestAlias;
  }
}
</script>
