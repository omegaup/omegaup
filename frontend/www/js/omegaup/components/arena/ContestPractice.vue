<template>
  <div data-contest-practice>
    <audio v-if="admin" class="notification-audio">
      <source src="/media/notification.mp3" type="audio/mpeg" />
    </audio>
    <div class="title">
      <h1>
        <span>{{ contest.title }}</span>
        <sup class="socket-status" title="WebSocket">•</sup>
      </h1>
      <div class="clock">∞</div>
    </div>
    <div class="tab navleft">
      <div class="navbar">
        <omegaup-arena-navbar-problems
          :problems="problems"
          :active-problem="activeProblem"
          :in-assignment="false"
          :digits-after-decimal-point="contest.partialScore ? 2 : 0"
          @disable-active-problem="activeProblem = null"
          @navigate-to-problem="onNavigateProblem"
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
          :problem="problem"
          :active-tab="'problems'"
          :runs="runs"
          :arena-mode="ArenaMode.Practice"
        ></omegaup-problem-details>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import arena_NavbarProblems from './NavbarProblems.vue';
import arena_ContestSummary from './ContestSummaryV2.vue';
import problem_Details, { ArenaMode } from '../problem/Details.vue';

@Component({
  components: {
    'omegaup-arena-contest-summary': arena_ContestSummary,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-problem-details': problem_Details,
  },
})
export default class ArenaContestPractice extends Vue {
  @Prop() contest!: types.ContestPublicDetails;
  @Prop() problems!: types.NavbarContestProblem[];
  @Prop() problem!: types.ProblemInfo;
  @Prop({ default: false }) isEphemeralExperimentEnabled!: boolean;
  @Prop({ default: false }) admin!: boolean;
  @Prop({ default: true }) showNavigation!: boolean;
  @Prop({ default: false }) showRanking!: boolean;
  @Prop({ default: true }) showClarifications!: boolean;
  @Prop({ default: true }) showDeadlines!: boolean;

  ArenaMode = ArenaMode;
  T = T;
  activeProblem: string | null = this.problem?.alias ?? null;
  runs: types.Run[] | undefined = [];

  onNavigateProblem(problemAlias: string) {
    this.$emit('navigate-to-problem', this, problemAlias);
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
  color: #080;
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
