<template>
  <omegaup-arena
    :active-tab="activeTab"
    :contest-title="currentAssignment.name"
    :should-show-runs="course.is_admin || course.is_curator"
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
              :digits-after-decimal-point="2"
              @disable-active-problem="activeProblem = null"
              @navigate-to-problem="onNavigateToProblem"
            ></omegaup-arena-navbar-problems>
          </div>
          <omegaup-arena-assignment-summary
            v-if="activeProblem === null"
            :assignment="currentAssignment"
          ></omegaup-arena-assignment-summary>
        </div>
      </div>
    </template>
  </omegaup-arena>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import arena_Arena from './Arena.vue';
import arena_NavbarProblems from './NavbarProblems.vue';
import arena_AssignmentSummary from './AssignmentSummary.vue';

export interface ActiveProblem {
  runs: types.Run[];
  problem: types.NavbarProblemsetProblem;
}

@Component({
  components: {
    'omegaup-arena': arena_Arena,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-arena-assignment-summary': arena_AssignmentSummary,
  },
})
export default class ArenaContestPractice extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() currentAssignment!: types.ArenaAssignment;
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: null }) problem!: ActiveProblem | null;
  @Prop() problemInfo!: types.ProblemInfo;
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];
  @Prop() activeTab!: string;
  @Prop({ default: null }) guid!: null | string;

  T = T;
  activeProblem: ActiveProblem | null = this.problem;

  get activeProblemAlias(): null | string {
    return this.activeProblem?.problem.alias ?? null;
  }

  onNavigateToProblem(request: ActiveProblem) {
    this.activeProblem = request;
    this.$emit('navigate-to-problem', request);
  }
}
</script>
