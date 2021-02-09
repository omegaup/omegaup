<template>
  <omegaup-arena
    :active-tab="activeTab"
    :contest-title="contest.title"
    :is-admin="isAdmin"
    :arena-mode="'practice'"
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
              :digits-after-decimal-point="contest.partialScore ? 2 : 0"
              @disable-active-problem="activeProblem = null"
              @navigate-to-problem="onNavigateToProblem"
            ></omegaup-arena-navbar-problems>
          </div>
          <omegaup-arena-contest-summary
            v-if="activeProblem === null"
            :contest="contest"
            :show-ranking="false"
          ></omegaup-arena-contest-summary>
          <div v-else class="problem main">
            <omegaup-problem-details
              :user="{ loggedIn: true, admin: false, reviewer: false }"
              :problem="problemInfo"
              :active-tab="'problems'"
              :runs="activeProblem.runs"
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
    <template #arena-clarifications>
      <omegaup-arena-clarification-list
        :problems="problems"
        :clarifications="currentClarifications"
        :is-admin="contestAdmin"
        :in-contest="true"
        @new-clarification="
          (newClarification) => $emit('new-clarification', newClarification)
        "
        @clarification-response="
          (id, responseText, isPublic) =>
            $emit('clarification-response', id, responseText, isPublic)
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
import arena_ContestSummary from './ContestSummaryV2.vue';
import omegaup_Markdown from '../Markdown.vue';
import problem_Details from '../problem/Details.vue';

export interface ActiveProblem {
  runs: types.Run[];
  alias: string;
}

@Component({
  components: {
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena': arena_Arena,
    'omegaup-arena-contest-summary': arena_ContestSummary,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-details': problem_Details,
  },
})
export default class ArenaContestPractice extends Vue {
  @Prop() contest!: types.ContestPublicDetails;
  @Prop() contestAdmin!: boolean;
  @Prop() problems!: types.NavbarProblemsetProblem[];
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
  @Prop() activeTab!: string;

  T = T;
  ui = ui;
  currentClarifications = this.clarifications;
  activeProblem: ActiveProblem | null = this.problem;

  get activeProblemAlias(): null | string {
    return this.activeProblem?.alias ?? null;
  }

  onNavigateToProblem(problemAlias: string) {
    this.activeProblem = { alias: problemAlias, runs: [] };
    this.$emit('navigate-to-problem', this.activeProblem);
  }

  @Watch('problem')
  onActiveProblemChanged(newValue: ActiveProblem | null): void {
    if (!newValue) {
      this.activeProblem = null;
      return;
    }
    this.onNavigateToProblem(newValue.alias);
  }

  @Watch('clarifications')
  onCarificationsChanged(newValue: types.Clarification[]): void {
    this.currentClarifications = newValue;
  }
}
</script>

<style lang="scss" scoped>
[data-contest-practice] {
  background: #668 url(/media/gradient.png) repeat-x 0 0;
  font-family: sans-serif;
  overflow-y: auto;
}

.navleft {
  overflow: hidden;
}

.navleft .navbar {
  width: 21em;
  float: left;
  background: transparent;
}

.navleft .main {
  margin-left: 20em;
  border: 1px solid #ccc;
  border-width: 0 0 1px 1px;
}

.problem {
  background: #fff;
  padding: 1em;
  margin-top: -1.5em;
  margin-right: -1em;
}
</style>
