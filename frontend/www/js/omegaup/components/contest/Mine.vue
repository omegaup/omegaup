<template>
  <div>
    <div
      class="alert alert-info alert-dismissable fade show"
      v-if="privateContestsAlert"
      role="alert"
    >
      {{ T.messageMakeYourContestsPublic }}
      <button
        type="button"
        class="close"
        data-dismiss="alert"
        aria-label="Close"
      >
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="card">
      <h5 class="card-header">{{ T.wordsMyContests }}</h5>
      <div class="card-body">
        <div class="row align-items-center justify-content-between">
          <div class="form-check col-7">
            <label class="form-check-label">
              <input
                class="form-check-input"
                type="checkbox"
                v-model="shouldShowAllContests"
                v-on:change.prevent="
                  $emit('change-show-all-contests', shouldShowAllContests)
                "
              />
              <span>{{ T.contestListShowAdminContests }}</span>
            </label>
          </div>
          <select
            class="custom-select col-5"
            v-model="allContestsVisibilityOption"
            v-on:change="onChangeAdmissionMode"
          >
            <option selected value="none">{{ T.forSelectedItems }}</option>
            <option value="public">{{ T.makePublic }}</option>
            <option value="private">{{ T.makePrivate }}</option>
            <option value="registration">{{ T.makeRegistration }}</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col" class="text-center align-middle">
                {{ T.wordsTitle }}
              </th>
              <th scope="col" class="text-center align-middle">
                {{ T.arenaPracticeStartTime }}
              </th>
              <th scope="col" class="text-center align-middle">
                {{ T.arenaPracticeEndtime }}
              </th>
              <th scope="col" class="text-center align-middle">
                {{ T.contestNewFormAdmissionMode }}
              </th>
              <th scope="col" class="text-center align-middle">
                {{ T.wordsScoreboard }}
              </th>
              <th scope="col" class="text-center align-middle">
                {{ T.wordsActions }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="contest in contests">
              <td class="d-flex align-items-center">
                <input
                  type="checkbox"
                  v-model="selectedContests"
                  v-bind:value="contest.alias"
                />
                <div class="d-inline-block ml-2">
                  <a class="mr-1" v-bind:href="`/arena/${contest.alias}/`">{{
                    ui.contestTitle(contest)
                  }}</a>
                </div>
              </td>
              <td>
                <a
                  v-bind:href="`https://timeanddate.com/worldclock/fixedtime.html?iso='${contest.start_time.toISOString()}`"
                  >{{ time.formatDateTime(contest.start_time) }}</a
                >
              </td>
              <td>
                <a
                  v-bind:href="`https://timeanddate.com/worldclock/fixedtime.html?iso='${contest.finish_time.toISOString()}`"
                  >{{ time.formatDateTime(contest.finish_time) }}</a
                >
              </td>
              <td class="text-center">
                {{ getAdmissionModeText(contest.admission_mode) }}
              </td>
              <td>
                <a
                  v-bind:href="`/arena/${contest.alias}/scoreboard/${contest.scoreboard_url}/`"
                  v-if="contest.scoreboard_url"
                >
                  <font-awesome-icon
                    v-bind:title="T.contestScoreboardLink"
                    v-bind:icon="['fas', 'link']"
                  />{{ T.wordsPublic }}
                </a>
                <a
                  class="ml-1"
                  v-bind:href="`/arena/${contest.alias}/scoreboard/${contest.scoreboard_url_admin}/`"
                  v-if="contest.scoreboard_url_admin"
                >
                  <font-awesome-icon
                    v-bind:title="T.contestScoreboardAdminLink"
                    v-bind:icon="['fas', 'link']"
                  />{{ T.wordsAdmin }}
                </a>
              </td>
              <td>
                <a v-bind:href="`/contest/${contest.alias}/edit/`">
                  <font-awesome-icon
                    v-bind:title="T.wordsEdit"
                    v-bind:icon="['fas', 'edit']"
                  />
                </a>
                <a
                  class="ml-2"
                  v-bind:href="`/arena/${contest.alias}/admin/#runs`"
                >
                  <font-awesome-icon
                    v-bind:title="T.contestListSubmissions"
                    v-bind:icon="['fas', 'tachometer-alt']"
                  />
                </a>
                <a
                  class="ml-2"
                  v-bind:href="`/contest/${contest.alias}/stats/`"
                >
                  <font-awesome-icon
                    v-bind:title="T.profileStatistics"
                    v-bind:icon="['fas', 'chart-bar']"
                  />
                </a>
                <a
                  class="ml-2"
                  v-bind:href="`/contest/${contest.alias}/activity/`"
                >
                  <font-awesome-icon
                    v-bind:title="T.activityReport"
                    v-bind:icon="['fas', 'clock']"
                  />
                </a>
                <a class="ml-2" v-bind:href="`/arena/${contest.alias}/print/`">
                  <font-awesome-icon
                    v-bind:title="T.contestPrintableVersion"
                    v-bind:icon="['fas', 'print']"
                  />
                </a>
                <a
                  class="ml-2"
                  href="#"
                  v-on:click="onDownloadCsv(contest.alias)"
                >
                  <font-awesome-icon
                    v-bind:title="T.contestDownloadListOfUsersInContest"
                    v-bind:icon="['fas', 'file-download']"
                  />
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEdit,
  faLink,
  faTachometerAlt,
  faChartBar,
  faClock,
  faPrint,
  faFileDownload,
} from '@fortawesome/free-solid-svg-icons';
library.add(
  faEdit,
  faLink,
  faTachometerAlt,
  faChartBar,
  faClock,
  faPrint,
  faFileDownload,
);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class List extends Vue {
  @Prop() contests!: types.ContestListItem[];
  @Prop() privateContestsAlert!: boolean;

  T = T;
  ui = ui;
  time = time;
  shouldShowAllContests = false;
  allContestsVisibilityOption = 'none';
  selectedContests: string[] = [];

  getAdmissionModeText(admissionMode: string): string {
    switch (admissionMode) {
      case 'public':
        return T.wordsPublic;
      case 'private':
        return T.wordsPrivate;
      case 'registration':
        return T.wordsRegistration;
      default:
        return '';
    }
  }

  onChangeAdmissionMode(): void {
    if (
      this.allContestsVisibilityOption !== 'none' &&
      this.selectedContests.length
    ) {
      this.$emit(
        'change-admission-mode',
        this.selectedContests,
        this.allContestsVisibilityOption,
      );
      this.selectedContests = [];
      this.allContestsVisibilityOption = 'none';
    }
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
