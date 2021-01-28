<template>
  <div data-contest-practice>
    <div class="title">
      <h1>
        <span>{{ contest.title }}</span>
        <sup class="socket-status" title="WebSocket">✗</sup>
      </h1>
      <div class="clock">∞</div>
    </div>
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
          :should-show-clarifications="true"
        >
          <template #quality-nomination-buttons><div></div></template>
          <template #best-solvers-list><div></div></template>
          <template #arena-scoreboard>
            <div class="card">
              <div class="card-body">
                <omegaup-markdown
                  :markdown="
                    ui.formatString(
                      T.arenaContestPracticeOriginalScoreboardText,
                      { contestAlias: contest.alias },
                    )
                  "
                ></omegaup-markdown>
              </div>
            </div>
          </template>
          <template #clarifications-list>
            <omegaup-arena-clarification-list
              :clarifications="clarifications"
              :in-contest="true"
              @clarification-response="
                (id, responseText, isPublic) =>
                  $emit('clarification-response', id, responseText, isPublic)
              "
            ></omegaup-arena-clarification-list>
          </template>
        </omegaup-problem-details>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';
import arena_ClarificationList from '../arena/ClarificationList.vue';
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
    'omegaup-arena-contest-summary': arena_ContestSummary,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-details': problem_Details,
  },
})
export default class ArenaContestPractice extends Vue {
  @Prop() contest!: types.ContestPublicDetails;
  @Prop() problems!: types.NavbarContestProblem[];
  @Prop({ default: null }) problem!: ActiveProblem | null;
  @Prop() problemInfo!: types.ProblemInfo;
  @Prop({ default: false }) isEphemeralExperimentEnabled!: boolean;
  @Prop({ default: false }) admin!: boolean;
  @Prop({ default: true }) showNavigation!: boolean;
  @Prop({ default: false }) showRanking!: boolean;
  @Prop({ default: true }) showClarifications!: boolean;
  @Prop({ default: true }) showDeadlines!: boolean;

  T = T;
  ui = ui;
  activeProblem: ActiveProblem | null = this.problem;

  get activeProblemAlias(): null | string {
    return this.activeProblem?.alias ?? null;
  }

  onNavigateToProblem(problemAlias: string) {
    this.activeProblem = { alias: problemAlias, runs: [] };
    this.$emit('navigate-to-problem', this.activeProblem);
  }
}
</script>

<style lang="scss" scoped>
[data-contest-practice] {
  background: #668 url(/media/gradient.png) repeat-x 0 0;
  font-family: sans-serif;
  overflow-y: auto;
}

.title {
  min-height: 80px;
  h1 {
    text-align: center;
    font-size: 2em;
    margin: 0.5em;
  }
}

.socket-status {
  color: #800;
}

.title,
.clock {
  text-align: center;
}

.clock {
  font-size: 6em;
  line-height: 0.4em;
  margin-bottom: 0.2em;
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
