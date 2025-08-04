<template>
  <omegaup-arena
    :active-tab="activeTab"
    :title="contest.title"
    :should-show-runs="contestAdmin"
    :background-class="'practice'"
    @update:activeTab="(selectedTab) => $emit('update:activeTab', selectedTab)"
  >
    <template #arena-problems>
      <div data-contest-practice>
        <div class="tab navleft">
          <div class="navbar">
            <omegaup-arena-navbar-problems
              :problems="problems"
              :active-problem="activeProblemAlias"
              :in-assignment="false"
              :digits-after-decimal-point="digitsAfterDecimalPoint"
              @disable-active-problem="activeProblem = null"
              @navigate-to-problem="onNavigateToProblem"
            ></omegaup-arena-navbar-problems>
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
              :problem="problemInfo"
              :active-tab="'problems'"
              :runs="runs"
              :popup-displayed="popupDisplayed"
              :guid="guid"
              :problem-alias="problemAlias"
              :contest-alias="contest.alias"
              :in-contest-or-course="true"
              :run-details-data="currentRunDetailsData"
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
      <div class="card">
        <div class="card-body">
          <omegaup-markdown
            :markdown="
              ui.formatString(T.arenaContestPracticeOriginalScoreboardText, {
                contestAlias: contest.alias,
              })
            "
          ></omegaup-markdown>
        </div>
      </div>
    </template>
    <template #arena-runs>
      <div class="card">
        <div class="card-body">
          <omegaup-markdown
            :markdown="
              ui.formatString(T.arenaContestPracticeOriginalRunsText, {
                contestAlias: contest.alias,
              })
            "
          ></omegaup-markdown>
        </div>
      </div>
    </template>
    <template #arena-clarifications>
      <omegaup-arena-clarification-list
        :problems="problems"
        :users="users"
        :problem-alias="problems.length != 0 ? problems[0].alias : null"
        :username="contestAdmin && users.length != 0 ? users[0].username : null"
        :current-user-class-name="currentUserClassName"
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
        @clarification-response="onClarificationResponse"
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
import arena_Summary from './Summary.vue';
import omegaup_Markdown from '../Markdown.vue';
import problem_Details, { PopupDisplayed } from '../problem/Details.vue';
import { ContestClarificationType } from '../../arena/clarifications';
import { SubmissionRequest } from '../../arena/submissions';

@Component({
  components: {
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena': arena_Arena,
    'omegaup-arena-summary': arena_Summary,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-details': problem_Details,
  },
})
export default class ArenaContestPractice extends Vue {
  @Prop() contest!: types.ContestDetails;
  @Prop() contestAdmin!: boolean;
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: null }) problem!: types.NavbarProblemsetProblem | null;
  @Prop() problemInfo!: types.ProblemInfo;
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];
  @Prop({ default: false }) isEphemeralExperimentEnabled!: boolean;
  @Prop({ default: false }) showNewClarificationPopup!: boolean;
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop() activeTab!: string;
  @Prop({ default: null }) guid!: null | string;
  @Prop({ default: null }) problemAlias!: null | string;
  @Prop({ default: () => [] }) runs!: types.Run[];
  @Prop({ default: null }) runDetailsData!: null | types.RunDetails;
  @Prop({ default: null }) nextSubmissionTimestamp!: Date | null;
  @Prop({ default: null }) nextExecutionTimestamp!: Date | null;
  @Prop({ default: false })
  shouldShowFirstAssociatedIdentityRunWarning!: boolean;
  @Prop({ default: 'user-rank-unranked' }) currentUserClassName!: string;

  T = T;
  ui = ui;
  currentClarifications = this.clarifications;
  ContestClarificationType = ContestClarificationType;
  activeProblem: types.NavbarProblemsetProblem | null = this.problem;
  currentNextSubmissionTimestamp = this.nextSubmissionTimestamp;
  currentNextExecutionTimestamp = this.nextExecutionTimestamp;
  currentRunDetailsData = this.runDetailsData;

  get activeProblemAlias(): null | string {
    return this.activeProblem?.alias ?? null;
  }

  get digitsAfterDecimalPoint(): number {
    return this.contest.score_mode !== 'all_or_nothing' ? 2 : 0;
  }

  onNavigateToProblem(problem: types.NavbarProblemsetProblem) {
    this.activeProblem = problem;
    this.$emit('navigate-to-problem', { problem });
  }

  onRunSubmitted(run: { code: string; language: string }): void {
    this.$emit('submit-run', {
      ...run,
      problem: this.activeProblem,
      target: this,
    });
  }

  onRunDetails(request: SubmissionRequest): void {
    this.$emit('show-run', {
      ...request,
      hash: `#problems/${this.activeProblemAlias}/show-run:${request.guid}`,
    });
  }

  onClarificationResponse(response: types.Clarification): void {
    this.$emit('clarification-response', {
      contestAlias: this.contest.alias,
      clarification: response,
      contestClarificationRequest: {
        type: ContestClarificationType.AllProblems,
        contestAlias: this.contest.alias,
      },
    });
  }

  onRunExecuted(): void {
    this.$emit('execute-run', { target: this });
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
}

.navleft .navbar {
  background: transparent;
}

.navleft .main {
  border: 1px solid var(--arena-contest-navleft-main-border-color);
  border-width: 0 0 1px 1px;
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
