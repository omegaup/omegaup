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
          <omegaup-arena-summary
            v-if="activeProblem === null"
            :title="currentAssignment.name"
            :description="currentAssignment.description"
            :start-time="currentAssignment.start_time"
            :finish-time="currentAssignment.finish_time"
            :admin="currentAssignment.director"
          ></omegaup-arena-summary>
          <div v-else class="problem main">
            <omegaup-problem-details
              :user="{ loggedIn: true, admin: false, reviewerR: false }"
              :problem="problemInfo"
              :active-tab="'problems'"
              :runs="activeProblem.runs"
              :popup-displayed="popupDisplayed"
              :guid="guid"
              :problem-alias="problemAlias"
              :should-show-run-details="shouldShowRunDetails"
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
              <template #quality-nomination-buttons>
                <div></div>
              </template>
              <template #best-solvers-list>
                <div></div>
              </template>
            </omegaup-problem-details>
          </div>
        </div>
      </div>
    </template>
  </omegaup-arena>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import arena_Arena from './Arena.vue';
import arena_NavbarProblems from './NavbarProblems.vue';
import arena_Summary from './Summary.vue';
import problem_Details, { PopupDisplayed } from '../problem/Details.vue';

export interface ActiveProblem {
  runs: types.Run[];
  problem: types.NavbarProblemsetProblem;
}

@Component({
  components: {
    'omegaup-arena': arena_Arena,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-arena-summary': arena_Summary,
    'omegaup-problem-details': problem_Details,
  },
})
export default class ArenaCourse extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() currentAssignment!: types.ArenaAssignment;
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: null }) problem!: ActiveProblem | null;
  @Prop() problemInfo!: types.ProblemInfo;
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];
  @Prop() activeTab!: string;
  @Prop({ default: null }) guid!: null | string;
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop({ default: null }) problemAlias!: null | string;

  T = T;
  activeProblem: ActiveProblem | null = this.problem;
  shouldShowRunDetails = false;

  get activeProblemAlias(): null | string {
    return this.activeProblem?.problem.alias ?? null;
  }

  onNavigateToProblem(activeProblem: ActiveProblem) {
    this.activeProblem = activeProblem;
    this.$emit('navigate-to-problem', activeProblem);
  }

  onRunSubmitted(run: { code: string; language: string }): void {
    this.$emit('submit-run', Object.assign({}, run, this.activeProblem));
  }

  @Watch('popupDisplayed')
  onPopupDisplayedChanged(newValue: PopupDisplayed): void {
    if (newValue === PopupDisplayed.RunDetails) {
      this.$nextTick(() => {
        this.shouldShowRunDetails = true;
      });
    }
  }

  @Watch('problem')
  onActiveProblemChanged(newValue: ActiveProblem | null): void {
    if (!newValue) {
      this.activeProblem = null;
      return;
    }
    this.onNavigateToProblem(newValue);
  }
}
</script>
