<template>
  <div class="mt-2" data-runs>
    <slot name="title">
      <div class="card-header">
        <h1 class="text-center">{{ T.wordsGlobalSubmissions }}</h1>
      </div>
    </slot>
    <div class="table-responsive">
      <table
        class="runs table table-striped"
        :class="{
          'single-problem-runs': !showAllRuns,
          'all-runs': showAllRuns,
        }"
      >
        <caption>
          {{
            T.wordsSubmissions
          }}
          <div v-if="showPager">
            <div class="pager-controls">
              <button
                data-button-page-previous
                :disabled="filterOffset <= 0"
                @click="filterOffset--"
              >
                &lt;
              </button>
              {{ currentPage }}
              <button
                data-button-page-next
                :disabled="
                  totalRuns && Math.ceil(totalRuns / rowCount) == currentPage
                "
                @click="filterOffset++"
              >
                &gt;
              </button>
            </div>
            <label
              >{{ T.wordsVerdict }}:
              <select
                v-model="filterVerdict"
                data-select-verdict
                class="form-control"
              >
                <option value="">{{ T.wordsAll }}</option>
                <option value="AC">AC</option>
                <option value="PA">PA</option>
                <option value="WA">WA</option>
                <option value="TLE">TLE</option>
                <option value="MLE">MLE</option>
                <option value="OLE">OLE</option>
                <option value="RTE">RTE</option>
                <option value="RFE">RFE</option>
                <option value="CE">CE</option>
                <option value="JE">JE</option>
                <option value="VE">VE</option>
                <option value="NO-AC">No AC</option>
              </select>
            </label>

            <label
              >{{ T.wordsStatus }}:
              <select
                v-model="filterStatus"
                data-select-status
                class="form-control"
              >
                <option value="">{{ T.wordsAll }}</option>
                <option value="new">new</option>
                <option value="waiting">waiting</option>
                <option value="compiling">compiling</option>
                <option value="running">running</option>
                <option value="ready">ready</option>
              </select>
            </label>

            <label
              >{{ T.wordsLanguage }}:
              <select
                v-model="filterLanguage"
                data-select-language
                class="form-control"
              >
                <option value="">{{ T.wordsAll }}</option>
                <option value="cpp20-gcc">C++20 (g++ 10.3)</option>
                <option value="cpp20-clang">C++20 (clang++ 10.0)</option>
                <option value="cpp17-gcc">C++17 (g++ 10.3)</option>
                <option value="cpp17-clang">C++17 (clang++ 10.0)</option>
                <option value="cpp11-gcc">C++11 (g++ 10.3)</option>
                <option value="cpp11-clang">C++11 (clang++ 10.0)</option>
                <option value="c11-gcc">C (gcc 10.3)</option>
                <option value="c11-clang">C (clang 10.0)</option>
                <option value="cs">C# (10, dotnet 6.0)</option>
                <option value="hs">Haskell (ghc 8.8)</option>
                <option value="java">Java (openjdk 16.0)</option>
                <option value="kt">Kotlin (1.6.10)</option>
                <option value="pas">Pascal (fpc 3.0)</option>
                <option value="py3">Python (3.9)</option>
                <option value="py2">Python (2.7)</option>
                <option value="rb">Ruby (2.7)</option>
                <option value="lua">Lua (5.3)</option>
                <option value="go">Go (1.18.beta2)</option>
                <option value="rs">Rust (1.56.1)</option>
                <option value="js">JavaScript (Node.js 16)</option>
                <option value="kp">Karel (Pascal)</option>
                <option value="kj">Karel (Java)</option>
                <option value="cat">{{ T.wordsJustOutput }}</option>
              </select>
            </label>

            <template v-if="showProblem">
              <label
                >{{ T.wordsProblem }}:
                <omegaup-common-typeahead
                  data-search-problem
                  :existing-options="searchResultProblems"
                  :value.sync="filterProblem"
                  @update-existing-options="
                    (query) => $emit('update-search-result-problems', query)
                  "
                ></omegaup-common-typeahead>
              </label>
              <button
                type="button"
                class="close"
                style="float: none"
                @click="filterProblem = null"
              >
                &times;
              </button>
            </template>

            <template v-if="showUser">
              <label
                >{{ T.contestParticipant }}:
                <omegaup-common-typeahead
                  data-search-username
                  :existing-options="searchResultUsers"
                  :value.sync="filterUsername"
                  :max-results="10"
                  @update-existing-options="updateSearchResultUsers"
                ></omegaup-common-typeahead>
              </label>
            </template>

            <div class="row">
              <div
                v-if="filtersExcludingOffset.length > 0"
                class="col-sm col-12"
              >
                <span
                  v-for="filter in filtersExcludingOffset"
                  :key="filter.name"
                  class="btn-secondary mr-3"
                >
                  <span class="mr-2"
                    >{{ filter.name }}: {{ filter.value }}</span
                  >
                  <a
                    :data-remove-filter="filter.name"
                    @click="onRemoveFilter(filter.name)"
                  >
                    <font-awesome-icon :icon="['fas', 'times']" />
                  </a>
                </span>
                <a
                  href="#runs"
                  data-remove-all-filters
                  @click="onRemoveFilter('all')"
                >
                  <span class="mr-2">{{ T.wordsRemoveFilter }}</span>
                </a>
              </div>
            </div>
          </div>
        </caption>
        <thead>
          <tr>
            <th>{{ T.wordsTime }}</th>
            <th>GUID</th>
            <th v-if="showUser">{{ T.contestParticipant }}</th>
            <th v-if="showContest">{{ T.wordsContest }}</th>
            <th v-if="showProblem">{{ T.wordsProblem }}</th>
            <th>{{ T.wordsStatus }}</th>
            <th v-if="showPoints" class="numeric">{{ T.wordsPoints }}</th>
            <th v-if="showPoints" class="numeric">{{ T.wordsPenalty }}</th>
            <th v-if="!showPoints" class="numeric">{{ T.wordsPercentage }}</th>
            <th>{{ T.wordsLanguage }}</th>
            <th class="numeric">{{ T.wordsMemory }}</th>
            <th class="numeric">{{ T.wordsRuntime }}</th>
            <th v-if="showDetails && !showDisqualify && !showRejudge">
              {{ T.arenaRunsActions }}
            </th>
            <th v-else></th>
          </tr>
        </thead>
        <tfoot v-if="problemAlias != null">
          <tr>
            <td colspan="10" data-new-run>
              <a
                v-if="isContestFinished"
                :href="`/arena/${contestAlias}/practice/`"
                >{{ T.arenaContestEndedUsePractice }}</a
              >
              <button
                v-else-if="useNewSubmissionButton"
                class="w-100"
                @click="$emit('new-submission')"
              >
                {{ newSubmissionDescription }}
              </button>
              <a
                v-else
                :href="newSubmissionUrl"
                @click="$emit('new-submission')"
                >{{ newSubmissionDescription }}</a
              >
            </td>
          </tr>
        </tfoot>
        <tbody>
          <tr v-for="run in filteredRuns" :key="run.guid">
            <td>{{ time.formatDateLocalHHMM(run.time) }}</td>
            <td>
              <acronym :title="run.guid" data-run-guid>
                <tt>{{ run.guid.substring(0, 8) }}</tt>
              </acronym>
            </td>
            <td
              v-if="showUser"
              class="text-break-all"
              :data-username="run.username"
            >
              <omegaup-user-username
                :classname="run.classname"
                :username="run.username"
                :country="run.country_id"
                :linkify="true"
                :emit-click-event="true"
                @click="
                  (username) =>
                    (filterUsername = { key: username, value: username })
                "
              ></omegaup-user-username>
              <a :href="`/profile/${run.username}/`" class="ml-2">
                <font-awesome-icon :icon="['fas', 'external-link-alt']" />
              </a>
            </td>
            <td v-if="showContest" class="text-break-all">
              <a
                href="#runs"
                @click="
                  onEmitFilterChanged({
                    filter: 'contest',
                    value: run.contest_alias,
                  })
                "
                >{{ run.contest_alias }}</a
              >
              <a
                v-if="run.contest_alias"
                :href="`/arena/${run.contest_alias}/`"
                class="ml-2"
              >
                <font-awesome-icon :icon="['fas', 'external-link-alt']" />
              </a>
            </td>
            <td v-if="showProblem" class="text-break-all">
              <a href="#runs" @click.prevent="filterProblem.key = run.alias">{{
                run.alias
              }}</a>
              <a :href="`/arena/problem/${run.alias}/`" class="ml-2">
                <font-awesome-icon :icon="['fas', 'external-link-alt']" />
              </a>
            </td>
            <td
              :class="statusClass(run)"
              data-run-status
              class="text-center opacity-4 font-weight-bold"
            >
              <span class="mr-1">{{ status(run) }}</span>

              <button
                v-if="!!statusHelp(run)"
                type="button"
                :data-content="statusHelp(run)"
                data-toggle="popover"
                data-trigger="focus"
                class="btn-outline-dark btn-sm"
                @click="showVerdictHelp"
              >
                <font-awesome-icon :icon="['fas', 'question-circle']" />
              </button>
            </td>
            <td v-if="showPoints" class="numeric">{{ points(run) }}</td>
            <td v-if="showPoints" class="numeric">{{ penalty(run) }}</td>
            <td v-if="!showPoints" class="numeric">{{ percentage(run) }}</td>
            <td>{{ run.language }}</td>
            <td class="numeric">{{ memory(run) }}</td>
            <td class="numeric">{{ runtime(run) }}</td>
            <td v-if="showDetails && !showDisqualify && !showRejudge">
              <button
                class="details btn-outline-dark btn-sm"
                :data-run-details="run.guid"
                @click="onRunDetails(run)"
              >
                <font-awesome-icon :icon="['fas', 'search-plus']" />
              </button>
              <button
                v-if="requestFeedback"
                class="details btn-outline-dark btn-sm"
                @click="$emit('request-feedback', run.guid)"
              >
                <font-awesome-icon
                  :title="T.courseRequestFeedback"
                  icon="comment-dots"
                />
              </button>
            </td>
            <td
              v-else-if="showDetails || showDisqualify || showRejudge"
              :data-actions="run.guid"
            >
              <div class="dropdown">
                <button
                  data-runs-actions-button
                  class="btn-secondary dropdown-toggle"
                  type="button"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  {{ T.arenaRunsActions }}
                </button>
                <div class="dropdown-menu">
                  <button
                    v-if="showDetails"
                    data-runs-show-details-button
                    :data-run-details="run.guid"
                    class="btn-link dropdown-item"
                    @click="onRunDetails(run)"
                  >
                    {{ T.arenaRunsActionsDetails }}
                  </button>
                  <button
                    v-if="showRejudge"
                    :data-actions-rejudge="run.guid"
                    class="btn-link dropdown-item"
                    @click="$emit('rejudge', run)"
                  >
                    {{ T.arenaRunsActionsRejudge }}
                  </button>
                  <template v-if="showDisqualify">
                    <div class="dropdown-divider"></div>
                    <button
                      v-if="run.type === 'normal'"
                      :data-actions-disqualify="run.guid"
                      class="btn-link dropdown-item"
                      @click="$emit('disqualify', run)"
                    >
                      {{ T.arenaRunsActionsDisqualify }}
                    </button>
                    <button
                      v-else-if="run.type === 'disqualified'"
                      :data-actions-requalify="run.guid"
                      class="btn-link dropdown-item"
                      @click="$emit('requalify', run)"
                    >
                      {{ T.arenaRunsActionsRequalify }}
                    </button>
                  </template>
                </div>
              </div>
            </td>
            <td v-else></td>
          </tr>
        </tbody>
      </table>
    </div>
    <slot name="runs">
      <omegaup-overlay
        :show-overlay="currentPopupDisplayed !== PopupDisplayed.None"
        @hide-overlay="onPopupDismissed"
      >
        <template #popup>
          <omegaup-arena-rundetails-popup
            v-show="currentPopupDisplayed === PopupDisplayed.RunDetails"
            :data="currentRunDetailsData"
            @dismiss="onPopupDismissed"
          ></omegaup-arena-rundetails-popup>
        </template>
      </omegaup-overlay>
    </slot>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch, Emit } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as time from '../../time';
import user_Username from '../user/Username.vue';
import common_Typeahead from '../common/Typeahead.vue';
import arena_RunDetailsPopup from './RunDetailsPopup.vue';
import omegaup_Overlay from '../Overlay.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faQuestionCircle,
  faRedoAlt,
  faBan,
  faSearchPlus,
  faExternalLinkAlt,
  faTimes,
} from '@fortawesome/free-solid-svg-icons';
library.add(faQuestionCircle);
library.add(faRedoAlt);
library.add(faBan);
library.add(faSearchPlus);
library.add(faExternalLinkAlt);
library.add(faTimes);

declare global {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface JQuery {
    popover(action: string): JQuery;
  }
}

export enum PopupDisplayed {
  None,
  RunSubmit,
  RunDetails,
  Promotion,
  Demotion,
  Reviewer,
}

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-arena-rundetails-popup': arena_RunDetailsPopup,
    'omegaup-overlay': omegaup_Overlay,
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-user-username': user_Username,
  },
})
export default class Runs extends Vue {
  @Prop({ default: false }) isContestFinished!: boolean;
  @Prop({ default: true }) isProblemsetOpened!: boolean;
  @Prop({ default: false }) showContest!: boolean;
  @Prop({ default: false }) showDetails!: boolean;
  @Prop({ default: false }) showDisqualify!: boolean;
  @Prop({ default: false }) showPager!: boolean;
  @Prop({ default: false }) showPoints!: boolean;
  @Prop({ default: false }) showProblem!: boolean;
  @Prop({ default: false }) showRejudge!: boolean;
  @Prop({ default: false }) showUser!: boolean;
  @Prop({ default: false }) useNewSubmissionButton!: boolean;
  @Prop({ default: null }) contestAlias!: string | null;
  @Prop({ default: null }) problemAlias!: string | null;
  @Prop({ default: () => [] }) problemsetProblems!: types.ProblemsetProblem[];
  @Prop({ default: null }) username!: string | null;
  @Prop({ default: 100 }) rowCount!: number;
  @Prop() runs!: null | types.Run[];
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop({ default: null }) runDetailsData!: types.RunDetails | null;
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop({ default: null }) guid!: null | string;
  @Prop({ default: false }) showAllRuns!: boolean;
  @Prop() totalRuns!: number;
  @Prop() searchResultProblems!: types.ListItem[];
  @Prop() requestFeedback!: boolean;

  PopupDisplayed = PopupDisplayed;
  T = T;
  time = time;

  filterLanguage: string = '';
  filterOffset: number = 0;
  filterProblem: null | types.ListItem = null;
  filterStatus: string = '';
  filterUsername: null | types.ListItem = null;
  filterVerdict: string = '';
  filterContest: string = '';
  filters: { name: string; value: string }[] = [];
  currentRunDetailsData = this.runDetailsData;
  currentPopupDisplayed = this.popupDisplayed;

  get currentPage(): number {
    return this.filterOffset + 1;
  }

  get filteredRuns(): types.Run[] {
    if (
      !this.filterLanguage &&
      !this.filterProblem &&
      !this.filterStatus &&
      !this.filterUsername &&
      !this.filterContest &&
      !this.filterVerdict
    ) {
      return this.sortedRuns;
    }
    return this.sortedRuns.filter((run) => {
      if (this.filterVerdict) {
        if (this.filterVerdict == 'NO-AC') {
          if (run.verdict == 'AC') {
            return false;
          }
        } else if (run.verdict != this.filterVerdict) {
          return false;
        }
      }
      if (this.filterLanguage && run.language !== this.filterLanguage) {
        return false;
      }
      if (this.filterProblem && run.alias !== this.filterProblem.key) {
        return false;
      }
      if (this.filterStatus && run.status !== this.filterStatus) {
        return false;
      }
      if (this.filterUsername && run.username !== this.filterUsername.key) {
        return false;
      }
      if (this.filterContest && run.contest_alias !== this.filterContest) {
        return false;
      }
      return true;
    });
  }

  get filtersExcludingOffset(): { name: string; value: string }[] {
    return this.filters.filter((filter) => filter.name !== 'offset');
  }

  get sortedRuns(): types.Run[] {
    if (!this.runs) {
      return [];
    }
    return this.runs
      .slice()
      .sort((a, b) => b.time.getTime() - a.time.getTime());
  }

  get newSubmissionUrl(): string {
    if (this.isProblemsetOpened) {
      return `#problems/${this.problemAlias}/new-run`;
    }
    return `/arena/${this.contestAlias}/`;
  }

  get newSubmissionDescription(): string {
    if (this.isProblemsetOpened) {
      return T.wordsNewSubmissions;
    }
    return T.arenaContestNotOpened;
  }

  memory(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      let prefix = '';
      if (run.verdict == 'MLE') {
        prefix = '>';
      }
      return `${prefix}${(run.memory / (1024 * 1024)).toFixed(2)} MB`;
    } else {
      return '—';
    }
  }

  penalty(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      return run.penalty.toFixed(2);
    }
    return '—';
  }

  percentage(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      return `${(run.score * 100).toFixed(2)}%`;
    }
    return '—';
  }

  points(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE' &&
      typeof run.contest_score !== 'undefined'
    ) {
      return run.contest_score.toFixed(2);
    }
    return '—';
  }

  runtime(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      let prefix = '';
      if (run.verdict == 'TLE') {
        prefix = '>';
      }
      return `${prefix}${(run.runtime / 1000).toFixed(2)} s`;
    }
    return '—';
  }

  showVerdictHelp(ev: Event): void {
    $(ev.target as HTMLElement).popover('show');
  }

  statusClass(run: types.Run): string {
    if (run.status != 'ready') return '';
    if (run.type == 'disqualified') return 'status-disqualified';
    if (run.verdict == 'AC') {
      return 'status-ac';
    }
    if (run.verdict == 'CE') {
      return 'status-ce';
    }
    if (run.verdict == 'JE' || run.verdict == 'VE') {
      return 'status-je-ve';
    }
    return '';
  }

  status(run: types.Run): string {
    if (run.type == 'disqualified') return T.arenaRunsActionsDisqualified;

    return run.status == 'ready' ? run.verdict : run.status;
  }

  statusHelp(run: types.Run): string {
    if (run.status != 'ready' || run.verdict == 'AC') {
      return '';
    }

    if (run.language == 'kj' || run.language == 'kp') {
      if (run.verdict == 'RTE' || run.verdict == 'RE') {
        return T.verdictHelpKarelRTE;
      } else if (run.verdict == 'TLE' || run.verdict == 'TO') {
        return T.verdictHelpKarelTLE;
      }
    }
    if (run.type == 'disqualified') return T.verdictHelpDisqualified;
    const verdict = T[`verdict${run.verdict}`];
    const verdictHelp = T[`verdictHelp${run.verdict}`];

    return `${verdict}: ${verdictHelp}`;
  }

  onRunDetails(run: types.Run): void {
    this.$emit('details', {
      guid: run.guid,
      isAdmin: true,
      hash: `#runs/all/show-run:${run.guid}`,
    });
    this.currentPopupDisplayed = PopupDisplayed.RunDetails;
  }

  onPopupDismissed(): void {
    this.currentPopupDisplayed = PopupDisplayed.None;
    this.currentRunDetailsData = null;
    this.$emit('reset-hash');
  }

  @Watch('runDetailsData')
  onRunDetailsChanged(newValue: types.RunDetails): void {
    this.currentRunDetailsData = newValue;
  }

  @Watch('username')
  onUsernameChanged(newValue: string | null) {
    if (!newValue) {
      this.filterUsername = null;
      return;
    }
    this.filterUsername = { key: newValue, value: newValue };
  }

  @Watch('problemAlias')
  onProblemAliasChanged(newValue: string | null) {
    if (!newValue) {
      this.filterProblem = null;
      return;
    }
    this.filterProblem = { key: newValue, value: newValue };
  }

  @Watch('filterLanguage')
  onFilterLanguageChanged(newValue: string) {
    this.onEmitFilterChanged({ filter: 'language', value: newValue });
  }

  @Watch('filterOffset')
  onFilterOffsetChanged(newValue: number) {
    this.$emit('filter-changed', { filter: 'offset', value: `${newValue}` });
  }

  @Watch('filterProblem')
  onFilterProblemChanged(newValue: null | types.ListItem) {
    if (!newValue) {
      this.onEmitFilterChanged({ filter: 'problem', value: null });
      return;
    }
    this.onEmitFilterChanged({ filter: 'problem', value: newValue.key });
  }

  @Watch('filterStatus')
  onFilterStatusChanged(newValue: string) {
    this.onEmitFilterChanged({ filter: 'status', value: newValue });
  }

  @Watch('filterUsername')
  onFilterUsernameChanged(newValue: null | types.ListItem) {
    if (!newValue) {
      this.onEmitFilterChanged({ filter: 'username', value: null });
      return;
    }
    this.onEmitFilterChanged({ filter: 'username', value: newValue.key });
  }

  @Watch('filterVerdict')
  onFilterVerdictChanged(newValue: string) {
    this.onEmitFilterChanged({ filter: 'verdict', value: newValue });
  }

  @Emit('filter-changed')
  onEmitFilterChanged({
    filter,
    value,
  }: {
    filter: string;
    value: null | string;
  }): void {
    this.filterOffset = 0;
    if (!value) {
      this.filters = this.filters.filter((item) => item.name !== filter);
      return;
    }
    if (filter === 'contest') {
      // This field does not appear as filter
      this.filterContest = value;
    }
    const currentFilter = this.filters.find((item) => item.name === filter);
    if (!currentFilter) {
      this.filters.push({ name: filter, value: value });
    } else {
      currentFilter.value = value;
    }
  }

  onRemoveFilter(filter: string): void {
    if (filter === 'all') {
      this.filterLanguage = '';
      this.filterProblem = null;
      this.filterStatus = '';
      this.filterUsername = null;
      this.filterVerdict = '';
      this.filterContest = '';
      this.filterOffset = 0;

      this.filters = [];
      return;
    }
    switch (filter) {
      case 'language':
        this.filterLanguage = '';
        break;
      case 'problem':
        this.filterProblem = null;
        break;
      case 'status':
        this.filterStatus = '';
        break;
      case 'username':
        this.filterUsername = null;
        break;
      case 'verdict':
        this.filterVerdict = '';
        break;
      case 'contest':
        this.filterContest = '';
    }
    this.filters = this.filters.filter((item) => item.name !== filter);
  }

  updateSearchResultUsers(query: string): void {
    if (this.problemsetProblems.length !== 0 && this.contestAlias) {
      this.$emit('update-search-result-users-contest', {
        query,
        contestAlias: this.contestAlias,
      });
      return;
    }
    this.$emit('update-search-result-users', { query });
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
caption {
  caption-side: top;
}

.text-break-all {
  word-break: break-all;
}

.runs {
  width: 100%;
  border: 1px solid var(--arena-runs-table-border-color);
  margin-top: 2em;
}

.runs caption {
  font-weight: bold;
  font-size: 1em;
  margin-bottom: 1em;
}

.runs td,
.runs th {
  border: 1px solid var(--arena-runs-table-td-border-color);
  border-width: 1px 0;
  text-align: center;
}

.runs tfoot td {
  a,
  button {
    display: block;
    padding: 0.5em;
    text-decoration: none;
    color: var(--arena-runs-table-tfoot-font-color);
    background: var(--arena-runs-table-tfoot-background-color);
    text-align: center;
  }

  a:hover,
  button:hover {
    background: var(--arena-runs-table-tfoot-background-color--hoover);
  }
}

.status-disqualified {
  background: var(--arena-runs-table-status-disqualified-background-color);
  color: var(--arena-runs-table-status-disqualified-font-color);
}
.status-je-ve {
  background: var(--arena-runs-table-status-je-ve-background-color);
  color: var(--arena-runs-table-status-je-ve-font-color);
}
.status-ac {
  background: var(--arena-runs-table-status-ac-background-color);
  color: var(--arena-runs-table-status-ac-font-color);
}
.status-ce {
  background: var(--arena-runs-table-status-ce-background-color);
  color: var(--arena-runs-table-status-ce-font-color);
}
</style>
