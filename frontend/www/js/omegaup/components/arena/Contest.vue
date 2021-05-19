<template>
  <omegaup-arena
    :active-tab="activeTab"
    :contest-title="contest.title"
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
              :user="{ loggedIn: true, admin: false, reviewer: false }"
              :next-submission-timestamp="nextSubmissionTimestamp"
              :problem="problemInfo"
              :active-tab="'problems'"
              :runs="activeProblem.runs"
              :popup-displayed="popupDisplayed"
              :guid="guid"
              :should-show-run-details="shouldShowRunDetails"
              :contest-alias="contest.alias"
              :is-contest-finished="isContestFinished"
              @update:activeTab="
                (selectedTab) =>
                  $emit('reset-hash', {
                    selectedTab,
                    alias: activeProblem.problem.alias,
                  })
              "
              @submit-run="onRunSubmitted"
              @show-run="(source) => $emit('show-run', source)"
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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
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

export interface ActiveProblem {
  runs: types.Run[];
  problem: types.NavbarProblemsetProblem;
}

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
  @Prop() contest!: types.ContestPublicDetails;
  @Prop() contestAdmin!: boolean;
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: null }) problem!: ActiveProblem | null;
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

  T = T;
  ui = ui;
  ContestClarificationType = ContestClarificationType;
  currentClarifications = this.clarifications;
  activeProblem: ActiveProblem | null = this.problem;
  shouldShowRunDetails = false;
  nextSubmissionTimestamp: Date | null = null;
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
    return this.activeProblem?.problem.alias ?? null;
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

  onNavigateToProblem(request: ActiveProblem) {
    this.activeProblem = request;
    this.$emit('navigate-to-problem', request);
  }

  onRunSubmitted(request: { code: string; language: string }): void {
    this.$emit('submit-run', {
      ...request,
      ...this.activeProblem,
      target: this,
    });
  }

  @Watch('problem')
  onActiveProblemChanged(newValue: ActiveProblem | null): void {
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
    this.nextSubmissionTimestamp = newValue.nextSubmissionTimestamp ?? null;
  }

  @Watch('clarifications')
  onClarificationsChanged(newValue: types.Clarification[]): void {
    this.currentClarifications = newValue;
  }

  @Watch('popupDisplayed')
  onPopupDisplayedChanged(newValue: PopupDisplayed): void {
    if (newValue === PopupDisplayed.RunDetails) {
      this.$nextTick(() => {
        this.shouldShowRunDetails = true;
      });
    }
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
