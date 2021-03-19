<template>
  <omegaup-arena
    :active-tab="activeTab"
    :contest-title="contest.title"
    :is-admin="isAdmin"
    @update:activeTab="(selectedTab) => $emit('update:activeTab', selectedTab)"
  >
    <template #socket-status>
      <sup :class="socketClass" title="WebSocket">{{ socketIcon }}</sup>
    </template>
    <template #clock>
      <omegaup-countdown
        v-if="deadline"
        class="clock"
        :target-time="deadline"
      ></omegaup-countdown>
      <div v-else class="alert alert-warning" role="alert">
        <a :href="urlPractice">{{ T.arenaContestEndedUsePractice }}</a>
      </div>
    </template>
    <template #arena-problems>
      <div data-contest>
        <div class="tab navleft">
          <div class="navbar">
            <omegaup-arena-navbar-problems
              :problems="problems"
              :active-problem="activeProblemAlias"
              :in-assignment="false"
              :digits-after-decimal-point="contest.partialScore ? 2 : 0"
              @disable-active-problem="activeProblem = null"
              @navigate-to-problem="onNavigateToProblem"
            ></omegaup-arena-navbar-problems>
            <omegaup-arena-navbar-miniranking
              :users="minirankingUsers"
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
              :problem="problemInfo"
              :active-tab="'problems'"
              :runs="activeProblem.runs"
              :popup-displayed="popupDisplayed"
              :guid="guid"
              :should-show-run-details="shouldShowRunDetails"
              :is-contest-finished="!deadline"
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
        :in-contest="true"
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
  @Prop() minirankingUsers!: omegaup.UserRank[];
  @Prop() ranking!: types.ScoreboardRankingEntry[];
  @Prop() lastUpdated!: Date;
  @Prop() submissionDeadline!: Date;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;
  @Prop({ default: true }) showPenalty!: boolean;
  @Prop({ default: true }) socketConnected!: boolean;

  T = T;
  ui = ui;
  ContestClarificationType = ContestClarificationType;
  currentClarifications = this.clarifications;
  activeProblem: ActiveProblem | null = this.problem;
  shouldShowRunDetails = false;

  get socketIcon(): string {
    if (this.socketConnected) return '•';
    return '✗';
  }

  get socketClass(): string {
    if (this.socketConnected) return 'socket-status-ok';
    return 'socket-status-error';
  }

  get activeProblemAlias(): null | string {
    return this.activeProblem?.problem.alias ?? null;
  }

  get deadline(): Date | boolean {
    const deadline = this.submissionDeadline || this.contest.finish_time;
    const now = new Date();
    if (deadline < now) {
      return false;
    }
    return deadline;
  }

  get urlPractice(): string {
    return `/arena/${this.contest.alias}/practice/`;
  }

  onNavigateToProblem(request: ActiveProblem) {
    this.activeProblem = request;
    this.$emit('navigate-to-problem', request);
  }

  onRunSubmitted(run: { code: string; language: string }): void {
    this.$emit('submit-run', Object.assign({}, run, this.activeProblem));
  }

  @Watch('problem')
  onActiveProblemChanged(newValue: ActiveProblem | null): void {
    if (!newValue) {
      this.activeProblem = null;
      return;
    }
    this.onNavigateToProblem(newValue);
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
.navleft {
  overflow: hidden;
  .navbar {
    width: 21em;
    float: left;
    background: transparent;
  }
  .main {
    margin-left: 20em;
    border: 1px solid #ccc;
    border-width: 0 0 1px 1px;
  }
}

.nav-tabs {
  .nav-link {
    background-color: #ddd;
    border-top-color: #ddd;
  }
}

.problem {
  background: #fff;
  padding: 1em;
  margin-top: -1.5em;
  margin-right: -1em;
}
</style>
