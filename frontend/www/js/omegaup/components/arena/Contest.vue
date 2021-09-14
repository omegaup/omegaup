<template>
  <omegaup-arena
    :active-tab="activeTab"
    :contest-title="ui.contestTitle(contest)"
    :is-admin="isAdmin"
    :clarifications="currentClarifications"
    @update:activeTab="(selectedTab) => $emit('update:activeTab', selectedTab)"
  >
    <template #socket-status>
      <sup :class="socketClass" :title="socketStatusTitle">{{
        socketStatus
      }}</sup>
    </template>
    <template #clock>
      <div v-if="isContestFinished" class="alert alert-warning" role="alert">
        <a :href="urlPractice">{{ T.arenaContestEndedUsePractice }}</a>
      </div>
      <omegaup-countdown
        v-else
        class="clock"
        :target-time="deadline"
        @finish="now = Date.now()"
      ></omegaup-countdown>
    </template>
    <template #arena-problems>
      <div data-contest>
        <div class="tab navleft">
          <div class="navbar">
            <omegaup-arena-navbar-problems
              :problems="problems"
              :active-problem="activeProblemAlias"
              :in-assignment="false"
              :digits-after-decimal-point="
                contest.partial_score ? digitsAfterDecimalPoint : 0
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
              ref="problem-details"
              :user="{ loggedIn: true, admin: false, reviewer: false }"
              :next-submission-timestamp="currentNextSubmissionTimestamp"
              :languages="contest.languages.split(',')"
              :problem="problemInfo"
              :active-tab="'problems'"
              :runs="runs"
              :popup-displayed="popupDisplayed"
              :guid="guid"
              :run-details-data="runDetailsData"
              :contest-alias="contest.alias"
              :is-contest-finished="isContestFinished"
              @update:activeTab="
                (selectedTab) =>
                  $emit('reset-hash', {
                    selectedTab,
                    alias: activeProblemAlias,
                  })
              "
              @submit-run="onRunSubmitted"
              @show-run="onRunDetails"
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
        :show-invited-users-filter="true"
      ></omegaup-arena-scoreboard>
    </template>
    <template #arena-clarifications>
      <omegaup-arena-clarification-list
        :problems="problems"
        :users="users"
        :problem-alias="problems.length != 0 ? problems[0].alias : null"
        :username="contestAdmin && users.length != 0 ? users[0].username : null"
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
</template>

<script lang="ts">
import * as Highcharts from 'highcharts/highstock';
import { Vue, Component, Prop, Watch, Ref } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';
import arena_Arena from './Arena.vue';
import arena_ClarificationList from './ClarificationList.vue';
import arena_NavbarProblems from './NavbarProblems.vue';
import arena_NavbarMiniranking from './NavbarMiniranking.vue';
import arena_Summary from './Summary.vue';
import arena_Scoreboard from './Scoreboard.vue';
import omegaup_Countdown from '../Countdown.vue';
import omegaup_Markdown from '../Markdown.vue';
import problem_Details, { PopupDisplayed } from '../problem/Details.vue';
import { omegaup } from '../../omegaup';
import { ContestClarificationType } from '../../arena/clarifications';
import { SocketStatus } from '../../arena/events_socket';
import { SubmissionRequest } from '../../arena/submissions';

@Component({
  components: {
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena': arena_Arena,
    'omegaup-arena-summary': arena_Summary,
    'omegaup-arena-navbar-miniranking': arena_NavbarMiniranking,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-arena-scoreboard': arena_Scoreboard,
    'omegaup-countdown': omegaup_Countdown,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-details': problem_Details,
  },
})
export default class ArenaContest extends Vue {
  @Ref('problem-details') readonly problemDetails!: Vue;
  @Prop() contest!: types.ContestPublicDetails;
  @Prop() contestAdmin!: boolean;
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: null }) problem!: types.NavbarProblemsetProblem | null;
  @Prop() problemInfo!: types.ProblemInfo;
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];
  @Prop({ default: false }) isEphemeralExperimentEnabled!: boolean;
  @Prop({ default: false }) admin!: boolean;
  @Prop({ default: true }) showNavigation!: boolean;
  @Prop({ default: false }) showRanking!: boolean;
  @Prop({ default: true }) showClarifications!: boolean;
  @Prop({ default: true }) showDeadlines!: boolean;
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop({ default: false }) showNewClarificationPopup!: boolean;
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop() activeTab!: string;
  @Prop({ default: null }) guid!: null | string;
  @Prop() miniRankingUsers!: omegaup.UserRank[];
  @Prop() ranking!: types.ScoreboardRankingEntry[];
  @Prop() rankingChartOptions!: Highcharts.Options;
  @Prop() lastUpdated!: Date;
  @Prop() submissionDeadline!: Date;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;
  @Prop({ default: true }) showPenalty!: boolean;
  @Prop({ default: SocketStatus.Waiting }) socketStatus!: SocketStatus;
  @Prop({ default: true }) socketConnected!: boolean;
  @Prop({ default: () => [] }) runs!: types.Run[];
  @Prop({ default: null }) runDetailsData!: null | types.RunDetails;
  @Prop({ default: null }) nextSubmissionTimestamp!: Date | null;

  T = T;
  ui = ui;
  ContestClarificationType = ContestClarificationType;
  currentClarifications = this.clarifications;
  activeProblem: types.NavbarProblemsetProblem | null = this.problem;
  currentNextSubmissionTimestamp = this.nextSubmissionTimestamp;
  now = new Date();

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

  get urlPractice(): string {
    return `/arena/${this.contest.alias}/practice/`;
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

  onRunDetails(source: SubmissionRequest): void {
    this.$emit('show-run', {
      ...source,
      request: {
        ...source.request,
        hash: `#problems/${
          this.activeProblemAlias ?? source.request.problemAlias
        }/show-run:${source.request.guid}/`,
      },
    });
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
  }

  @Watch('clarifications')
  onClarificationsChanged(newValue: types.Clarification[]): void {
    this.currentClarifications = newValue;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.navleft {
  overflow: hidden;

  .navbar {
    width: 21em;
    float: left;
    background: transparent;
  }

  .main {
    margin-left: 20em;
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
  margin-top: -1.5em;
  margin-right: -1em;
}
</style>
