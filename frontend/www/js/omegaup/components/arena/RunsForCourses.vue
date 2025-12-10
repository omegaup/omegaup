<template>
  <div class="mt-2" data-runs>
    <slot name="title">
      <div class="card-header">
        <h1 class="text-center">{{ T.wordsGlobalSubmissions }}</h1>
      </div>
    </slot>
    <div
      class="px-2 px-sm-4 border-0"
      :class="{
        'single-problem-runs': !showAllRuns,
        'all-runs': showAllRuns,
      }"
    >
      <div>
        <span class="font-weight-bold">{{ T.wordsSubmissions }}</span>
        <div v-if="showFilters">
          <b-pagination
            v-if="showFilters"
            v-model="currentPage"
            size="sm"
            :total-rows="totalRows"
            :per-page="itemsPerPage"
            :limit="1"
            hide-goto-end-buttons
            @page-click="onPageClick"
          >
            <template #page="{ page, active }">
              <b v-if="active" data-page>{{ page }} - {{ totalPages }}</b>
              <i v-else>{{ page }}</i>
            </template>
          </b-pagination>
          <div class="filters row">
            <label class="col-3 col-sm pr-0 font-weight-bold">
              {{ T.wordsExecution }}
              <select
                v-model="filterExecution"
                data-select-execution
                class="form-control"
              >
                <option value="">{{ T.wordsAll }}</option>
                <option value="EXECUTION_JUDGE_ERROR">
                  {{ T.runDetailsJudgeError }}
                </option>
                <option value="EXECUTION_VALIDATOR_ERROR">
                  {{ T.runDetailsValidatorError }}
                </option>
                <option value="EXECUTION_COMPILATION_ERROR">
                  {{ T.runDetailsCompilationError }}
                </option>
                <option value="EXECUTION_RUNTIME_FUNCTION_ERROR">
                  {{ T.runDetailsRuntimeFunctionError }}
                </option>
                <option value="EXECUTION_RUNTIME_ERROR">
                  {{ T.runDetailsRuntimeError }}
                </option>
                <option value="EXECUTION_INTERRUPTED">
                  {{ T.runDetailsInterrupted }}
                </option>
                <option value="EXECUTION_FINISHED">
                  {{ T.runDetailsFinished }}
                </option>
              </select>
            </label>

            <label class="col-3 col-sm pr-0 font-weight-bold">
              {{ T.wordsOutput }}
              <select
                v-model="filterOutput"
                data-select-output
                class="form-control"
              >
                <option value="">{{ T.wordsAll }}</option>
                <option value="OUTPUT_EXCEEDED">
                  {{ T.runDetailsExceeded }}
                </option>
                <option value="OUTPUT_INCORRECT">
                  {{ T.runDetailsIncorrect }}
                </option>
                <option value="OUTPUT_INTERRUPTED">
                  {{ T.runDetailsInterrupted }}
                </option>
                <option value="OUTPUT_CORRECT">
                  {{ T.runDetailsCorrect }}
                </option>
              </select>
            </label>

            <label class="col-5 col-sm pr-0 font-weight-bold"
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
              <label class="col-6 col-sm pr-1 font-weight-bold"
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
              <label class="col-5 col-sm font-weight-bold"
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
          </div>

          <div class="row">
            <div v-if="filtersExcludingOffset.length > 0" class="col-sm col-12">
              <span
                v-for="filter in filtersExcludingOffset"
                :key="filter.name"
                class="btn-secondary mr-3"
              >
                <span class="mr-2">{{ filter.name }}: {{ filter.value }}</span>
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
      </div>

      <div class="table-responsive">
        <table class="table runs">
          <thead>
            <tr>
              <th>
                <font-awesome-icon :icon="['fas', 'calendar-alt']" />
                {{ T.wordsTime }}
              </th>
              <th v-show="showGUID">{{ T.runGUID }}</th>
              <th>{{ T.wordsLanguage }}</th>
              <th v-if="showUser">{{ T.contestParticipant }}</th>
              <th v-if="showContest">{{ T.wordsContest }}</th>
              <th v-if="showProblem">{{ T.wordsProblem }}</th>
              <th hidden>{{ T.wordsStatus }}</th>
              <th v-if="showPoints" class="numeric">{{ T.wordsPoints }}</th>
              <th v-if="showPoints" class="numeric">{{ T.wordsPenalty }}</th>
              <th v-if="!showPoints" class="numeric">
                {{ T.wordsPercentage }}
              </th>
              <th>{{ T.wordsExecution }}</th>
              <th>{{ T.wordsOutput }}</th>
              <th class="numeric">
                <font-awesome-icon :icon="['fas', 'database']" />
                {{ T.wordsMemory }}
              </th>
              <th class="numeric">
                <font-awesome-icon :icon="['fas', 'clock']" />
                {{ T.wordsRuntime }}
              </th>
              <th>
                {{ T.arenaRunsActions }}
              </th>
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
                  :disabled="!canSubmit"
                  :class="{ disabled: !canSubmit }"
                  @click="onSubmit"
                >
                  <omegaup-countdown
                    v-if="!canSubmit"
                    :target-time="nextSubmissionTimestamp"
                    :countdown-format="
                      omegaup.CountdownFormat.WaitBetweenUploadsSeconds
                    "
                    @finish="now = Date.now()"
                  ></omegaup-countdown>
                  <span v-else>{{ newSubmissionDescription }}</span>
                </button>
                <a
                  v-else-if="canSubmit"
                  :href="newSubmissionUrl"
                  @click="onSubmit"
                  >{{ newSubmissionDescription }}</a
                >
                <div v-else>
                  <omegaup-countdown
                    :target-time="nextSubmissionTimestamp"
                    :countdown-format="
                      omegaup.CountdownFormat.WaitBetweenUploadsSeconds
                    "
                    @finish="now = Date.now()"
                  ></omegaup-countdown>
                </div>
              </td>
            </tr>
          </tfoot>
          <tbody>
            <tr
              v-for="run in paginatedRuns"
              :key="run.guid"
              :class="{ selected: createdGuid === run.guid }"
            >
              <td>{{ time.formatDateLocalHHMM(run.time) }}</td>
              <td v-show="showGUID">
                <acronym :title="run.guid" data-run-guid>
                  <tt>{{ getShortGuid(run.guid) }}</tt>
                </acronym>
              </td>
              <td>{{ run.language }}</td>
              <td
                v-if="showUser"
                class="text-break-all text-nowrap"
                :data-username="run.username"
              >
                <omegaup-user-username
                  :classname="run.classname"
                  :username="run.username"
                  :country="run.country"
                  :linkify="true"
                  :href="'#runs'"
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
                <a
                  href="#runs"
                  @click="
                    onEmitFilterChanged({
                      filter: 'problem',
                      value: run.alias,
                    })
                  "
                  >{{ run.alias }}</a
                >
                <a :href="`/arena/problem/${run.alias}/`" class="ml-2">
                  <font-awesome-icon :icon="['fas', 'external-link-alt']" />
                </a>
              </td>
              <td
                :class="statusClass(run)"
                data-run-status
                class="text-center opacity-4 font-weight-bold"
                hidden
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
              <td
                v-if="!showPoints"
                :class="statusPercentageClass(run)"
                class="numeric"
                data-run-percentage
              >
                {{ percentage(run) }}
              </td>
              <td class="numeric">{{ execution(run) }}</td>
              <td class="numeric">
                {{ output(run) }}
                <br
                  v-if="outputIconColorStatus(run) != NumericOutputStatus.None"
                />
                <font-awesome-icon
                  v-if="
                    outputIconColorStatus(run) === NumericOutputStatus.Correct
                  "
                  :icon="['fas', 'check-circle']"
                  style="color: green"
                />
                <font-awesome-icon
                  v-else-if="
                    outputIconColorStatus(run) === NumericOutputStatus.Incorrect
                  "
                  :icon="['fas', 'times-circle']"
                  style="color: red"
                />
                <font-awesome-icon
                  v-else-if="
                    outputIconColorStatus(run) ===
                    NumericOutputStatus.Interrupted
                  "
                  :icon="['fas', 'exclamation-circle']"
                  style="color: orange"
                />
              </td>
              <td class="numeric">{{ memory(run) }}</td>
              <td class="numeric">{{ runtime(run) }}</td>
              <td v-if="showDetails && !showDisqualify && !showRejudge">
                <button
                  class="details btn-outline-dark btn-sm"
                  :data-run-details="run.guid"
                  @click="onRunDetails(run)"
                >
                  <font-awesome-icon :icon="['fas', 'search-plus']" />
                  <span
                    v-if="run.suggestions && run.suggestions > 0"
                    class="position-absolute badge badge-danger"
                    >{{ run.suggestions }}
                  </span>
                </button>
                <button
                  v-if="requestFeedback"
                  class="details btn-outline-dark btn-sm ml-1"
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
                <div class="d-inline-block mr-2">
                  <button
                    class="details btn-outline-dark btn-sm"
                    data-runs-show-details-button
                    :data-run-details="run.guid"
                    @click="onRunDetails(run)"
                  >
                    <font-awesome-icon :icon="['fas', 'search-plus']" />
                    <span
                      v-if="run.suggestions && run.suggestions > 0"
                      class="position-absolute badge badge-danger"
                      >{{ run.suggestions }}
                    </span>
                  </button>
                </div>
                <div class="dropdown d-inline-block">
                  <button
                    data-runs-actions-button
                    class="btn btn-secondary dropdown-toggle"
                    type="button"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    {{ T.arenaRunsActions }}
                  </button>
                  <div class="dropdown-menu">
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
                      <template v-if="run.type === 'normal'">
                        <button
                          :data-actions-disqualify="run.guid"
                          class="btn-link dropdown-item"
                          @click="
                            $emit('disqualify', {
                              run,
                              disqualificationType: DisqualificationType.ByGUID,
                            })
                          "
                        >
                          {{ T.arenaRunsActionsDisqualifyByGUID }}
                        </button>
                      </template>

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
        <b-pagination
          v-if="!showFilters"
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="itemsPerPage"
          align="center"
        ></b-pagination>
      </div>
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
import { omegaup } from '../../omegaup';
import * as ui from '../../ui';
import T from '../../lang';
import { types } from '../../api_types';
import * as time from '../../time';
import user_Username from '../user/Username.vue';
import common_Typeahead from '../common/Typeahead.vue';
import arena_RunDetailsPopup from './RunDetailsPopup.vue';
import { DisqualificationType } from './Runs.vue';
import omegaup_Countdown from '../Countdown.vue';
import omegaup_Overlay from '../Overlay.vue';

import { PaginationPlugin } from 'bootstrap-vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faQuestionCircle,
  faRedoAlt,
  faBan,
  faSearchPlus,
  faExternalLinkAlt,
  faTimes,
  faDatabase,
  faClock,
  faCalendarAlt,
  faCheckCircle,
  faTimesCircle,
} from '@fortawesome/free-solid-svg-icons';

library.add(faQuestionCircle);
library.add(faRedoAlt);
library.add(faBan);
library.add(faSearchPlus);
library.add(faExternalLinkAlt);
library.add(faTimes);
library.add(faDatabase);
library.add(faClock);
library.add(faCalendarAlt);
library.add(faCheckCircle);
library.add(faTimesCircle);

Vue.use(PaginationPlugin);

declare global {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface JQuery {
    popover(action: string): JQuery;
  }
}

export enum MemoryStatus {
  NotAvailable = 'MEMORY_NOT_AVAILABLE',
  Exceeded = 'MEMORY_EXCEEDED',
}

export enum RuntimeStatus {
  NotAvailable = 'RUNTIME_NOT_AVAILABLE',
  Exceeded = 'RUNTIME_EXCEEDED',
}

export enum ExecutionStatus {
  JudgeError = 'EXECUTION_JUDGE_ERROR',
  ValidatorError = 'EXECUTION_VALIDATOR_ERROR',
  CompilationError = 'EXECUTION_COMPILATION_ERROR',
  RuntimeFunctionError = 'EXECUTION_RUNTIME_FUNCTION_ERROR',
  RuntimeError = 'EXECUTION_RUNTIME_ERROR',
  Interrupted = 'EXECUTION_INTERRUPTED',
}

export enum NumericOutputStatus {
  None = 0,
  Correct = 1,
  Incorrect = 2,
  Interrupted = 3,
}

export enum StringOutputStatus {
  Exceeded = 'OUTPUT_EXCEEDED',
  Incorrect = 'OUTPUT_INCORRECT',
  Interrupted = 'OUTPUT_INTERRUPTED',
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
    'omegaup-countdown': omegaup_Countdown,
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-user-username': user_Username,
  },
})
export default class RunsForCourses extends Vue {
  @Prop({ default: false }) isContestFinished!: boolean;
  @Prop({ default: true }) isProblemsetOpened!: boolean;
  @Prop({ default: false }) showContest!: boolean;
  @Prop({ default: false }) showDetails!: boolean;
  @Prop({ default: false }) showDisqualify!: boolean;
  @Prop({ default: false }) showFilters!: boolean;
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
  @Prop({ default: 10 }) itemsPerPage!: number;
  @Prop({ default: false }) showGUID!: boolean;
  @Prop({ default: null }) createdGuid!: null | string;
  @Prop({ default: null }) nextSubmissionTimestamp!: null | Date;

  NumericOutputStatus = NumericOutputStatus;
  PopupDisplayed = PopupDisplayed;
  T = T;
  time = time;
  DisqualificationType = DisqualificationType;
  omegaup = omegaup;

  filterLanguage: string = '';
  filterOffset: number = 0;
  filterProblem: null | types.ListItem = null;
  filterUsername: null | types.ListItem = null;
  filterContest: string = '';
  filterExecution: string = '';
  filterOutput: string = '';
  filters: { name: string; value: string }[] = [];
  currentRunDetailsData = this.runDetailsData;
  currentPopupDisplayed = this.popupDisplayed;
  currentPage: number = 1;
  currentDataPage: number = 1;
  newFieldsLaunchDate: Date = new Date('2023-10-22');
  now: number = Date.now();

  get totalRows(): number {
    if (this.totalRuns === undefined) {
      return this.filteredRuns.length;
    }
    return this.totalRuns;
  }

  get totalPages(): number {
    if (this.totalRows > 0) {
      return Math.ceil(this.totalRows / this.itemsPerPage);
    }
    return 1;
  }

  get canSubmit(): boolean {
    if (!this.nextSubmissionTimestamp) {
      return true;
    }
    return this.nextSubmissionTimestamp.getTime() <= this.now;
  }

  onPageClick(bvEvent: any, page: number): void {
    if (page == this.currentPage - 1 || page == this.currentPage + 1) {
      if (this.currentPage + 1 == page) {
        this.filterOffset++;
      } else if (this.currentPage - 1 == page) {
        this.filterOffset--;
      }
    } else {
      bvEvent.preventDefault();
    }
  }

  get paginatedRuns(): types.Run[] {
    if (!this.showFilters) {
      this.currentDataPage = this.currentPage;
    }
    const startIndex = (this.currentDataPage - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    return this.filteredRuns.slice(startIndex, endIndex);
  }

  getShortGuid(guid: string): string {
    return guid.substring(0, 8);
  }

  get filteredRuns(): types.Run[] {
    if (
      !this.filterLanguage &&
      !this.filterProblem &&
      !this.filterUsername &&
      !this.filterContest &&
      !this.filterExecution &&
      !this.filterOutput
    ) {
      return this.sortedRuns;
    }
    return this.sortedRuns.filter((run) => {
      if (this.filterLanguage && run.language !== this.filterLanguage) {
        return false;
      }
      if (this.filterProblem && run.alias !== this.filterProblem.key) {
        return false;
      }
      if (this.filterUsername && run.username !== this.filterUsername.key) {
        return false;
      }
      if (this.filterContest && run.contest_alias !== this.filterContest) {
        return false;
      }
      if (this.filterExecution && run.execution !== this.filterExecution) {
        return false;
      }
      if (this.filterOutput && run.output !== this.filterOutput) {
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
      run.status !== 'ready' ||
      run.status_memory === MemoryStatus.NotAvailable
    )
      return '—';
    if (run.status_memory === MemoryStatus.Exceeded)
      return T.runDetailsExceeded;
    return `${(run.memory / (1024 * 1024)).toFixed(2)} MB`;
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
      run.status !== 'ready' ||
      run.status_runtime === RuntimeStatus.NotAvailable
    ) {
      return '—';
    }

    if (run.status_runtime === RuntimeStatus.Exceeded) {
      return T.runDetailsExceeded;
    }

    return `${(run.runtime / 1000).toFixed(2)} s`;
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

  execution(run: types.Run): string {
    if (run.time < this.newFieldsLaunchDate) {
      return T.runDetailsNotAvailable;
    }

    if (run.status !== 'ready') {
      return '—';
    }

    switch (run.execution) {
      case ExecutionStatus.JudgeError:
        return T.runDetailsJudgeError;
      case ExecutionStatus.ValidatorError:
        return T.runDetailsValidatorError;
      case ExecutionStatus.CompilationError:
        return T.runDetailsCompilationError;
      case ExecutionStatus.RuntimeFunctionError:
        return T.runDetailsRuntimeFunctionError;
      case ExecutionStatus.RuntimeError:
        return T.runDetailsRuntimeError;
      case ExecutionStatus.Interrupted:
        return T.runDetailsInterrupted;
      default:
        return T.runDetailsFinished;
    }
  }

  output(run: types.Run): string {
    if (run.time < this.newFieldsLaunchDate) {
      return T.runDetailsNotAvailable;
    }

    if (run.status !== 'ready') {
      return '—';
    }

    switch (run.output) {
      case StringOutputStatus.Exceeded:
        return T.runDetailsExceeded;
      case StringOutputStatus.Incorrect:
        return T.runDetailsIncorrect;
      case StringOutputStatus.Interrupted:
        return T.runDetailsInterrupted;
      default:
        return T.runDetailsCorrect;
    }
  }

  statusPercentageClass(run: types.Run): string {
    if (run.status !== 'ready') {
      return '';
    }

    if (run.verdict == 'JE' || run.verdict == 'VE') {
      return 'status-je-ve';
    }

    if (run.verdict == 'CE') {
      return 'status-ce';
    }

    if (run.type === 'disqualified') {
      return 'status-disqualified';
    }

    const scorePercentage = (run.score * 100).toFixed(2);

    if (scorePercentage !== '100.00') {
      return '';
    }
    if (run.verdict == 'AC') {
      return 'status-ac';
    }
    if (run.verdict == 'TLE') {
      return 'status-tle';
    }
    if (run.verdict == 'MLE') {
      return 'status-mle';
    }
    if (run.verdict == 'WA') {
      return 'status-wa';
    }
    return 'status-ac';
  }

  outputIconColorStatus(run: types.Run): number {
    if (
      !(run.status === 'ready' && run.output !== StringOutputStatus.Exceeded) ||
      run.time < this.newFieldsLaunchDate
    ) {
      return NumericOutputStatus.None;
    }

    if (run.output === StringOutputStatus.Incorrect) {
      return NumericOutputStatus.Incorrect;
    } else if (run.output === StringOutputStatus.Interrupted) {
      return NumericOutputStatus.Interrupted;
    }

    return NumericOutputStatus.Correct;
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

  @Watch('filterUsername')
  onFilterUsernameChanged(newValue: null | types.ListItem) {
    if (!newValue) {
      this.onEmitFilterChanged({ filter: 'username', value: null });
      return;
    }
    this.onEmitFilterChanged({ filter: 'username', value: newValue.key });
  }

  @Watch('filterExecution')
  onFilterExecutionChanged(newValue: string) {
    this.onEmitFilterChanged({ filter: 'execution', value: newValue });
  }

  @Watch('filterOutput')
  onfilterOutputChanged(newValue: string) {
    this.onEmitFilterChanged({ filter: 'output', value: newValue });
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

  @Watch('createdGuid')
  onCreatedGuidChanged(newValue: null | string) {
    if (!newValue) {
      return;
    }

    const singleProblemRuns = document.getElementsByClassName(
      'single-problem-runs',
    );
    if (singleProblemRuns.length === 0) {
      return;
    }
    singleProblemRuns[0].scrollIntoView({ behavior: 'smooth' });
    const selectedElements = document.getElementsByClassName('selected');
    setTimeout(() => {
      Array.from(selectedElements).forEach((element) => {
        element.classList.remove('selected');
      });
    }, 10000);
  }

  onSubmit(): void {
    if (!this.canSubmit && this.nextSubmissionTimestamp) {
      alert(
        ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
          submissionGap: Math.ceil(
            (this.nextSubmissionTimestamp.getTime() - Date.now()) / 1000,
          ),
        }),
      );
      return;
    }
    this.$emit('new-submission');
  }

  onRemoveFilter(filter: string): void {
    this.currentPage = 1;
    this.currentDataPage = 1;
    if (filter === 'all') {
      this.filterLanguage = '';
      this.filterProblem = null;
      this.filterUsername = null;
      this.filterContest = '';
      this.filterExecution = '';
      this.filterOutput = '';
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
      case 'username':
        this.filterUsername = null;
        break;
      case 'contest':
        this.filterContest = '';
        break;
      case 'execution':
        this.filterExecution = '';
        break;
      case 'output':
        this.filterOutput = '';
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

.selected {
  background-color: var(--arena-runs-table-row-selected-background-color);
  border: 2px solid var(--arena-runs-table-row-selected-border-color);
  border-radius: 8px;
  transition: background-color 10s ease, border-color 10s ease;
}

.text-break-all {
  word-break: break-all;
}

.runs {
  border: 1px solid var(--arena-runs-table-border-color);
  margin-top: 2em;
}

.runs.filters {
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

.status-wa {
  background: var(--arena-runs-table-status-wa-background-color);
  color: var(--arena-runs-table-status-ac-font-color);
}

.status-mle {
  background: var(--arena-runs-table-status-mle-background-color);
  color: var(--arena-runs-table-status-ac-font-color);
}

.status-tle {
  background: var(--arena-runs-table-status-tle-background-color);
  color: var(--arena-runs-table-status-ac-font-color);
}

.status-ce {
  background: var(--arena-runs-table-status-ce-background-color);
  color: var(--arena-runs-table-status-ce-font-color);
}

.status-pa {
  background: var(--arena-runs-table-status-pa-background-color);
  color: var(--arena-runs-table-status-ac-font-color);
}

.status-ole {
  background: var(--arena-runs-table-status-ole-background-color);
  color: var(--arena-runs-table-status-ac-font-color);
}

.status-rte {
  background: var(--arena-runs-table-status-rte-background-color);
  color: var(--arena-runs-table-status-ac-font-color);
}

.status-rfe {
  background: var(--arena-runs-table-status-rfe-background-color);
  color: var(--arena-runs-table-status-ac-font-color);
}
</style>
