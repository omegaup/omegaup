<template>
  <div>
    <!-- Show error UI when tab is blocked -->
    <div v-if="isBlocked" class="container mt-5">
      <div class="alert alert-danger text-center" role="alert">
        <h4 class="alert-heading">{{ T.arenaContestMultipleTabsDetected }}</h4>
        <p class="mb-0">{{ blockedMessage }}</p>
      </div>
    </div>

    <!-- Normal arena content when not blocked -->
    <omegaup-arena
      v-else
      :active-tab="activeTab"
      :title="ui.contestTitle(contest)"
      :clarifications="currentClarifications"
      :should-show-runs="contestAdmin"
      @update:activeTab="
        (selectedTab) => $emit('update:activeTab', selectedTab)
      "
    >
      <template #socket-status>
        <sup :class="socketClass" :title="socketStatusTitle">{{
          socketStatus
        }}</sup>
      </template>
      <template v-if="contestAdmin" #edit-button>
        <a
          class="edit-contest-button ml-2"
          :href="`/contest/${contest.alias}/edit/`"
        >
          <font-awesome-icon icon="edit" />
        </a>
      </template>
      <template #clock>
        <div v-if="isContestFinished" class="alert alert-warning" role="alert">
          <a :href="urlPractice">{{ T.arenaContestEndedUsePractice }}</a>
        </div>
        <omegaup-countdown
          v-show="!isContestStarted"
          :countdown-format="omegaup.CountdownFormat.ContestHasNotStarted"
          :target-time="contest.start_time"
          @finish="now = new Date()"
        ></omegaup-countdown>
        <omegaup-countdown
          v-show="isContestStarted"
          class="clock"
          :target-time="deadline"
          @finish="now = new Date()"
        ></omegaup-countdown>
      </template>
      <template #arena-problems>
        <div data-contest>
          <div class="tab navleft">
            <div class="navbar mb-2">
              <omegaup-arena-navbar-problems
                :problems="problems"
                :active-problem="activeProblemAlias"
                :in-assignment="false"
                :digits-after-decimal-point="
                  contest.score_mode == 'all_or_nothing'
                    ? 0
                    : digitsAfterDecimalPoint
                "
                @disable-active-problem="activeProblem = null"
                @navigate-to-problem="onNavigateToProblem"
              ></omegaup-arena-navbar-problems>
              <omegaup-arena-navbar-miniranking
                :users="miniRankingUsers"
                :show-ranking="true"
              ></omegaup-arena-navbar-miniranking>
            </div>
            <omegaup-arena-summary
              v-if="activeProblem === null"
              :title="ui.contestTitle(contest)"
              :description="contest.description"
              :start-time="contest.start_time"
              :finish-time="contest.finish_time"
              :scoreboard="contest.scoreboard"
              :window-length="contest.window_length"
              :admin="contest.director"
              :show-ranking="false"
            ></omegaup-arena-summary>
            <div v-else class="problem main">
              <omegaup-problem-details
                :user="{ loggedIn: true, admin: false, reviewer: false }"
                :next-submission-timestamp="currentNextSubmissionTimestamp"
                :next-execution-timestamp="currentNextExecutionTimestamp"
                :languages="contest.languages.split(',')"
                :problem="problemInfo"
                :active-tab="'problems'"
                :runs="runs"
                :popup-displayed="popupDisplayed"
                :guid="guid"
                :run-details-data="currentRunDetailsData"
                :contest-alias="contest.alias"
                :is-contest-finished="isContestFinished"
                :in-contest-or-course="true"
                :use-new-verdict-table="false"
                @update:activeTab="
                  (selectedTab) =>
                    $emit('reset-hash', {
                      selectedTab,
                      alias: activeProblemAlias,
                    })
                "
                @submit-run="onRunSubmitted"
                @execute-run="onRunExecuted"
                @show-run="onRunDetails"
                @new-submission-popup-displayed="
                  $emit('new-submission-popup-displayed')
                "
              >
                <template #quality-nomination-buttons><div></div></template>
                <template #best-solvers-list><div></div></template>
              </omegaup-problem-details>
            </div>
          </div>
        </div>
      </template>
      <template #arena-scoreboard>
        <omegaup-arena-scoreboard
          :problems="problems"
          :ranking="ranking"
          :ranking-chart-options="rankingChartOptions"
          :last-updated="lastUpdated"
          :digits-after-decimal-point="digitsAfterDecimalPoint"
          :show-penalty="showPenalty"
          :show-invited-users-filter="
            contest.admission_mode !== AdmissionMode.Private
          "
          :show-all-contestants="
            contest.default_show_all_contestants_in_scoreboard
          "
        >
          <template #scoreboard-header><div></div></template>
        </omegaup-arena-scoreboard>
      </template>
      <template #arena-runs>
        <omegaup-arena-runs
          v-if="contestAdmin"
          :contest-alias="contest.alias"
          :runs="allRuns"
          :total-runs="totalRuns"
          :show-all-runs="true"
          :show-contest="false"
          :show-problem="true"
          :show-details="true"
          :show-disqualify="true"
          :show-pager="true"
          :show-rejudge="true"
          :show-user="true"
          :problemset-problems="Object.values(problems)"
          :is-contest-finished="isContestFinished"
          :in-contest="true"
          :search-result-users="searchResultUsers"
          :search-result-problems="searchResultProblems"
          @details="(run) => onRunAdminDetails(run.guid)"
          @rejudge="(run) => $emit('rejudge', run)"
          @disqualify="(request) => $emit('disqualify', request)"
          @requalify="(run) => $emit('requalify', run)"
          @update-search-result-users-contest="
            (request) => $emit('update-search-result-users-contest', request)
          "
          @update-search-result-users="
            (request) => $emit('update-search-result-users', request)
          "
          @filter-changed="(request) => $emit('apply-filter', request)"
        >
          <template #title><div></div></template>
          <template #runs><div></div></template>
        </omegaup-arena-runs>
        <omegaup-overlay
          v-if="contestAdmin"
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
      </template>
      <template #arena-clarifications>
        <omegaup-arena-clarification-list
          :problems="problems"
          :users="users"
          :problem-alias="problems.length != 0 ? problems[0].alias : null"
          :username="
            contestAdmin && users.length != 0 ? users[0].username : null
          "
          :clarifications="currentClarifications"
          :is-admin="contestAdmin"
          :show-new-clarification-popup="showNewClarificationPopup"
          @new-clarification="
            (contestClarification) =>
              $emit('new-clarification', {
                ...contestClarification,
                contestClarificationRequest: {
                  type: ContestClarificationType.AllProblems,
                  contestAlias: contest.alias,
                },
              })
          "
          @clarification-response="
            (response) =>
              $emit('clarification-response', {
                contestAlias: contest.alias,
                clarification: response,
                contestClarificationRequest: {
                  type: ContestClarificationType.AllProblems,
                  contestAlias: contest.alias,
                },
              })
          "
          @update:activeTab="
            (selectedTab) => $emit('update:activeTab', selectedTab)
          "
        ></omegaup-arena-clarification-list>
      </template>
    </omegaup-arena>
  </div>
</template>

<script lang="ts">
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import * as Highcharts from 'highcharts/highstock';
import { Component, Prop, Vue, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import { ContestClarificationType } from '../../arena/clarifications';
import { SocketStatus } from '../../arena/events_socket';
import { SubmissionRequest } from '../../arena/submissions';
import T from '../../lang';
import { omegaup } from '../../omegaup';
import * as ui from '../../ui';
import arena_RunDetailsPopup from '../arena/RunDetailsPopup.vue';
import { AdmissionMode } from '../common/Publish.vue';
import omegaup_Countdown from '../Countdown.vue';
import omegaup_Markdown from '../Markdown.vue';
import omegaup_Overlay from '../Overlay.vue';
import problem_Details, { PopupDisplayed } from '../problem/Details.vue';
import arena_Arena from './Arena.vue';
import arena_ClarificationList from './ClarificationList.vue';
import arena_NavbarMiniranking from './NavbarMiniranking.vue';
import arena_NavbarProblems from './NavbarProblems.vue';
import arena_Runs from './Runs.vue';
import arena_Scoreboard from './Scoreboard.vue';
import arena_Summary from './Summary.vue';
library.add(fas);

@Component({
  components: {
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena': arena_Arena,
    'omegaup-arena-runs': arena_Runs,
    'omegaup-arena-summary': arena_Summary,
    'omegaup-arena-navbar-miniranking': arena_NavbarMiniranking,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-arena-rundetails-popup': arena_RunDetailsPopup,
    'omegaup-arena-scoreboard': arena_Scoreboard,
    'omegaup-countdown': omegaup_Countdown,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-overlay': omegaup_Overlay,
    'omegaup-problem-details': problem_Details,
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class ArenaContest extends Vue {
  @Prop() contest!: types.ContestPublicDetails;
  @Prop({ default: false }) contestAdmin!: boolean;
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: null }) problem!: types.NavbarProblemsetProblem | null;
  @Prop() problemInfo!: types.ProblemInfo;
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];
  @Prop({ default: false }) isEphemeralExperimentEnabled!: boolean;
  @Prop({ default: true }) showNavigation!: boolean;
  @Prop({ default: false }) showRanking!: boolean;
  @Prop({ default: true }) showClarifications!: boolean;
  @Prop({ default: true }) showDeadlines!: boolean;
  @Prop({ default: false }) showNewClarificationPopup!: boolean;
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop() activeTab!: string;
  @Prop() totalRuns!: number;
  @Prop({ default: null }) guid!: null | string;
  @Prop() miniRankingUsers!: omegaup.UserRank[];
  @Prop() ranking!: types.ScoreboardRankingEntry[];
  @Prop() rankingChartOptions!: Highcharts.Options;
  @Prop() lastUpdated!: Date;
  @Prop() submissionDeadline!: Date;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;
  @Prop({ default: true }) showPenalty!: boolean;
  @Prop({ default: SocketStatus.Waiting }) socketStatus!: SocketStatus;
  @Prop({ default: () => [] }) runs!: types.Run[];
  @Prop({ default: null }) allRuns!: null | types.Run[];
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop({ default: null }) runDetailsData!: null | types.RunDetails;
  @Prop({ default: null }) nextSubmissionTimestamp!: Date | null;
  @Prop({ default: null }) nextExecutionTimestamp!: Date | null;
  @Prop({ default: false }) lockdown!: boolean;
  @Prop({ default: false })
  shouldShowFirstAssociatedIdentityRunWarning!: boolean;
  @Prop({ default: false }) isBlocked!: boolean;
  @Prop({ default: null }) blockedMessage!: string | null;

  T = T;
  ui = ui;
  omegaup = omegaup;
  AdmissionMode = AdmissionMode;
  PopupDisplayed = PopupDisplayed;
  ContestClarificationType = ContestClarificationType;
  currentClarifications = this.clarifications;
  activeProblem: types.NavbarProblemsetProblem | null = this.problem;
  currentNextSubmissionTimestamp = this.nextSubmissionTimestamp;
  currentNextExecutionTimestamp = this.nextExecutionTimestamp;
  currentRunDetailsData = this.runDetailsData;
  now = new Date();
  currentPopupDisplayed = this.popupDisplayed;

  get socketClass(): string {
    if (this.socketStatus === SocketStatus.Connected) {
      return 'socket-status socket-status-ok';
    }
    if (this.socketStatus === SocketStatus.Failed) {
      return 'socket-status socket-status-error';
    }
    return 'socket-status';
  }

  get socketStatusTitle(): string {
    if (this.socketStatus === SocketStatus.Connected) {
      return T.socketStatusConnected;
    }
    if (this.socketStatus === SocketStatus.Failed) {
      return T.socketStatusFailed;
    }
    return T.socketStatusWaiting;
  }

  get activeProblemAlias(): null | string {
    return this.activeProblem?.alias ?? null;
  }

  get deadline(): Date {
    return this.submissionDeadline || this.contest.finish_time;
  }

  get isContestFinished(): boolean {
    return this.deadline < this.now;
  }

  get isContestStarted(): boolean {
    return this.contest.start_time < this.now;
  }

  get urlPractice(): string {
    return `/arena/${this.contest.alias}/practice/`;
  }

  get searchResultProblems(): types.ListItem[] {
    if (!this.problems.length) {
      return [];
    }
    return this.problems.map((problem) => ({
      key: problem.alias,
      value: problem.text,
    }));
  }

  created() {
    if (this.lockdown) {
      window.addEventListener('beforeunload', this.beforeWindowUnload);
    }
  }

  beforeDestroy() {
    if (this.lockdown) {
      window.removeEventListener('beforeunload', this.beforeWindowUnload);
    }
  }

  confirmLeave() {
    return window.confirm(T.lockdownMessageWarning);
  }

  beforeWindowUnload(e: BeforeUnloadEvent) {
    if (!this.confirmLeave()) {
      // Cancel the event
      e.preventDefault();
      // Chrome requires returnValue to be set
      e.returnValue = true;
    }
  }

  onNavigateToProblem(problem: types.NavbarProblemsetProblem) {
    this.activeProblem = problem;
    this.$emit('navigate-to-problem', { problem });
  }

  onRunSubmitted(request: { code: string; language: string }): void {
    this.$emit('submit-run', {
      ...request,
      problem: this.activeProblem,
      target: this,
    });
  }

  onRunExecuted(): void {
    this.$emit('execute-run', { target: this });
  }

  onRunAdminDetails(guid: string): void {
    this.$emit('show-run', {
      guid,
      hash: `#runs/all/show-run:${guid}`,
      isAdmin: this.contestAdmin,
    });
    this.currentPopupDisplayed = PopupDisplayed.RunDetails;
  }

  onRunDetails(request: SubmissionRequest): void {
    this.$emit('show-run', {
      ...request,
      hash: `#problems/${this.activeProblemAlias}/show-run:${request.guid}`,
      isAdmin: this.contestAdmin,
    });
  }

  onPopupDismissed(): void {
    this.currentPopupDisplayed = PopupDisplayed.None;
    this.currentRunDetailsData = null;
    this.$emit('reset-hash', { selectedTab: 'runs', alias: null });
  }

  @Watch('problem')
  onActiveProblemChanged(newValue: types.NavbarProblemsetProblem | null): void {
    if (!newValue) {
      this.activeProblem = null;
      return;
    }
    this.onNavigateToProblem(newValue);
  }

  @Watch('problemInfo')
  onProblemInfoChanged(newValue: types.ProblemInfo | null): void {
    if (!newValue) {
      return;
    }
    this.currentNextSubmissionTimestamp =
      newValue.nextSubmissionTimestamp ?? null;
    this.currentNextExecutionTimestamp =
      newValue.nextExecutionTimestamp ?? null;
  }

  @Watch('clarifications')
  onClarificationsChanged(newValue: types.Clarification[]): void {
    this.currentClarifications = newValue;
  }

  @Watch('runDetailsData')
  onRunDetailsChanged(newValue: types.RunDetails): void {
    this.currentRunDetailsData = newValue;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.navleft {
  overflow: hidden;

  .navbar {
    background: transparent;
    justify-content: center;
  }

  .main {
    border: 1px solid var(--arena-contest-navleft-main-border-color);
    border-width: 0 0 1px 1px;
  }
}

.nav-tabs {
  .nav-link {
    background-color: var(--arena-contest-navtabs-link-background-color);
    border-top-color: var(--arena-contest-navtabs-link-border-top-color);
  }
}

.problem {
  background: var(--arena-problem-background-color);
  padding: 1em;
}

@media only screen and (min-width: 960px) {
  .navleft {
    .navbar {
      width: 21em;
      float: left;
    }
    .main {
      margin-left: 20em;
    }
  }
  .problem {
    margin-top: -1.5em;
    margin-right: -1em;
  }
}
</style>
