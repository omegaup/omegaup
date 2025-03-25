<template>
  <div>
    <div
      v-if="privateContestsAlert"
      class="alert alert-info alert-dismissable fade show"
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
    <h3 class="text-center mb-4">{{ T.wordsMyContests }}</h3>
    <div class="card">
      <div class="card-header">
        <div class="row align-items-center justify-content-around">
          <div class="form-check col-md-5">
            <label class="form-check-label">
              <input
                v-model="shouldShowAllContests"
                class="form-check-input"
                type="checkbox"
                @change.prevent="
                  $emit('change-show-all-contests', shouldShowAllContests)
                "
              />
              <span>{{ T.contestListShowAdminContests }}</span>
            </label>
          </div>
          <div class="form-check col-md-3">
            <label class="form-check-label">
              <input
                v-model="shouldShowArchivedContests"
                class="form-check-input"
                type="checkbox"
                @change.prevent="
                  $emit(
                    'change-show-archived-contests',
                    shouldShowArchivedContests,
                  )
                "
              />
              <span>{{ T.contestListArchivedContests }}</span>
            </label>
          </div>
          <select
            v-model="allContestsVisibilityOption"
            class="custom-select col-md-3"
            @change="onChangeAdmissionMode"
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
            <tr v-for="contest in contests" :key="contest.alias">
              <td class="d-flex align-items-center">
                <input
                  v-model="selectedContests"
                  type="checkbox"
                  :value="contest.alias"
                />
                <div class="d-inline-block ml-2">
                  <a class="mr-1" :href="ui.contestURL(contest)">{{
                    ui.contestTitle(contest)
                  }}</a>
                </div>
              </td>
              <td>
                <a
                  :href="`https://timeanddate.com/worldclock/fixedtime.html?iso='${contest.start_time.toISOString()}`"
                  >{{ time.formatDateTime(contest.start_time) }}</a
                >
              </td>
              <td>
                <a
                  :href="`https://timeanddate.com/worldclock/fixedtime.html?iso='${contest.finish_time.toISOString()}`"
                  >{{ time.formatDateTime(contest.finish_time) }}</a
                >
              </td>
              <td class="text-center">
                {{ getAdmissionModeText(contest.admission_mode) }}
              </td>
              <td>
                <a
                  v-if="contest.scoreboard_url"
                  :href="`/arena/${contest.alias}/scoreboard/${contest.scoreboard_url}/`"
                >
                  <font-awesome-icon
                    :title="T.contestScoreboardLink"
                    :icon="['fas', 'link']"
                  />{{ T.wordsPublic }}
                </a>
                <a
                  v-if="contest.scoreboard_url_admin"
                  class="ml-1"
                  :href="`/arena/${contest.alias}/scoreboard/${contest.scoreboard_url_admin}/`"
                >
                  <font-awesome-icon
                    :title="T.contestScoreboardAdminLink"
                    :icon="['fas', 'link']"
                  />{{ T.wordsAdmin }}
                </a>
              </td>
              <td>
                <a :href="`/contest/${contest.alias}/edit/`">
                  <font-awesome-icon
                    :title="T.wordsEdit"
                    :icon="['fas', 'edit']"
                  />
                </a>
                <a class="ml-2" :href="`/arena/${contest.alias}/#runs`">
                  <font-awesome-icon
                    :title="T.contestListSubmissions"
                    :icon="['fas', 'tachometer-alt']"
                  />
                </a>
                <a class="ml-2" :href="`/contest/${contest.alias}/stats/`">
                  <font-awesome-icon
                    :title="T.profileStatistics"
                    :icon="['fas', 'chart-bar']"
                  />
                </a>
                <a class="ml-2" :href="`/contest/${contest.alias}/activity/`">
                  <font-awesome-icon
                    :title="T.activityReport"
                    :icon="['fas', 'clock']"
                  />
                </a>
                <a class="ml-2" :href="`/arena/${contest.alias}/print/`">
                  <font-awesome-icon
                    :title="T.contestPrintableVersion"
                    :icon="['fas', 'print']"
                  />
                </a>
                <a class="ml-2" href="#" @click="onDownloadCsv(contest.alias)">
                  <font-awesome-icon
                    :title="T.contestDownloadListOfUsersInContest"
                    :icon="['fas', 'file-download']"
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
  shouldShowArchivedContests = false;
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
