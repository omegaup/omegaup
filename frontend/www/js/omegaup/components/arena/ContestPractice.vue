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
import { defineComponent, PropType } from 'vue';
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

export default defineComponent({
  name: 'ArenaContestPractice',
  components: {
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena': arena_Arena,
    'omegaup-arena-summary': arena_Summary,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-details': problem_Details,
  },
  props: {
    contest: {
      type: Object as PropType<types.ContestPublicDetails>,
      required: true,
    },
    contestAdmin: {
      type: Boolean,
      default: false,
    },
    problems: {
      type: Array as PropType<types.NavbarProblemsetProblem[]>,
      required: true,
    },
    users: {
      type: Array as PropType<types.ContestUser[]>,
      default: () => [],
    },
    problem: {
      type: (Object as unknown) as PropType<types.NavbarProblemsetProblem | null>,
      default: null,
    },
    problemInfo: {
      type: Object as PropType<types.ProblemInfo>,
      default: undefined,
    },
    clarifications: {
      type: Array as PropType<types.Clarification[]>,
      default: () => [],
    },
    isEphemeralExperimentEnabled: {
      type: Boolean,
      default: false,
    },
    showNewClarificationPopup: {
      type: Boolean,
      default: false,
    },
    popupDisplayed: {
      type: (Number as unknown) as PropType<PopupDisplayed>,
      default: PopupDisplayed.None,
    },
    activeTab: {
      type: String,
      default: 'problems',
    },
    guid: {
      type: (String as unknown) as PropType<null | string>,
      default: null,
    },
    problemAlias: {
      type: (String as unknown) as PropType<null | string>,
      default: null,
    },
    runs: {
      type: Array as PropType<types.Run[]>,
      default: () => [],
    },
    runDetailsData: {
      type: (Object as unknown) as PropType<null | types.RunDetails>,
      default: null,
    },
    nextSubmissionTimestamp: {
      type: (Date as unknown) as PropType<Date | null>,
      default: null,
    },
    nextExecutionTimestamp: {
      type: (Date as unknown) as PropType<Date | null>,
      default: null,
    },
    shouldShowFirstAssociatedIdentityRunWarning: {
      type: Boolean,
      default: false,
    },
  },
  emits: [
    'update:activeTab',
    'navigate-to-problem',
    'submit-run',
    'show-run',
    'clarification-response',
    'execute-run',
    'reset-hash',
    'new-submission-popup-displayed',
    'new-clarification',
  ],
  data() {
    return {
      T,
      ui,
      currentClarifications: this.clarifications,
      ContestClarificationType,
      activeProblem: this.problem as types.NavbarProblemsetProblem | null,
      currentNextSubmissionTimestamp: this
        .nextSubmissionTimestamp as Date | null,
      currentNextExecutionTimestamp: this.nextExecutionTimestamp as Date | null,
      currentRunDetailsData: this.runDetailsData as types.RunDetails | null,
    };
  },
  computed: {
    activeProblemAlias(): null | string {
      return this.activeProblem?.alias ?? null;
    },
    digitsAfterDecimalPoint(): number {
      return this.contest.score_mode !== 'all_or_nothing' ? 2 : 0;
    },
  },
  watch: {
    problem(newValue: types.NavbarProblemsetProblem | null): void {
      if (!newValue) {
        this.activeProblem = null;
        return;
      }
      this.onNavigateToProblem(newValue);
    },
    problemInfo(newValue: types.ProblemInfo | null): void {
      if (!newValue) {
        return;
      }
      this.currentNextSubmissionTimestamp =
        newValue.nextSubmissionTimestamp ?? null;
      this.currentNextExecutionTimestamp =
        newValue.nextExecutionTimestamp ?? null;
    },
    clarifications(newValue: types.Clarification[]): void {
      this.currentClarifications = newValue;
    },
    runDetailsData(newValue: types.RunDetails): void {
      this.currentRunDetailsData = newValue;
    },
  },
  methods: {
    onNavigateToProblem(problem: types.NavbarProblemsetProblem): void {
      this.activeProblem = problem;
      this.$emit('navigate-to-problem', { problem });
    },
    onRunSubmitted(run: { code: string; language: string }): void {
      this.$emit('submit-run', {
        ...run,
        problem: this.activeProblem,
        target: this,
      });
    },
    onRunDetails(request: SubmissionRequest): void {
      this.$emit('show-run', {
        ...request,
        hash: `#problems/${this.activeProblemAlias}/show-run:${request.guid}`,
      });
    },
    onClarificationResponse(response: types.Clarification): void {
      this.$emit('clarification-response', {
        contestAlias: this.contest.alias,
        clarification: response,
        contestClarificationRequest: {
          type: ContestClarificationType.AllProblems,
          contestAlias: this.contest.alias,
        },
      });
    },
    onRunExecuted(): void {
      this.$emit('execute-run', { target: this });
    },
  },
});
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
