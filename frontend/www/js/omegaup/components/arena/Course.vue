<template>
  <omegaup-arena
    :active-tab="activeTab"
    :contest-title="currentAssignment.name"
    :should-show-runs="course.is_admin || course.is_curator"
    @update:activeTab="(selectedTab) => $emit('update:activeTab', selectedTab)"
  >
    <template #socket-status>
      <sup :class="socketClass" title="WebSocket">{{ socketIcon }}</sup>
    </template>
    <template #clock>
      <div class="clock">{{ clock }}</div>
    </template>
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
              :user="{ loggedIn: true, admin: false, reviewer: false }"
              :problem="problemInfo"
              :active-tab="'problems'"
              :runs="activeProblem.runs"
              :guid="guid"
              :problem-alias="problemAlias"
              :should-show-run-details="shouldShowRunDetails"
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
    <template #arena-clarifications>
      <div class="container">
        <omegaup-arena-clarification-list
          :problems="problems"
          :users="users"
          :problem-alias="problems.length != 0 ? problems[0].alias : null"
          :username="
            (course.is_admin || course.is_curator) && users.length != 0
              ? users[0].username
              : null
          "
          :clarifications="currentClarifications"
          :is-admin="course.is_admin || course.is_curator"
          :allow-filter-by-assignment="true"
          :show-new-clarification-popup="showNewClarificationPopup"
          @new-clarification="(request) => $emit('new-clarification', request)"
          @clarification-response="
            (request) => $emit('clarification-response', request)
          "
          @update:activeTab="
            (selectedTab) => $emit('update:activeTab', selectedTab)
          "
        >
          <template #table-title>
            <th class="text-center" scope="col">{{ T.wordsHomework }}</th>
            <th class="text-center" scope="col">{{ T.wordsProblem }}</th>
          </template>
        </omegaup-arena-clarification-list>
      </div>
    </template>
  </omegaup-arena>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import arena_Arena from './Arena.vue';
import arena_ClarificationList from './ClarificationList.vue';
import arena_NavbarProblems from './NavbarProblems.vue';
import arena_Summary from './Summary.vue';
import problem_Details from '../problem/Details.vue';

@Component({
  components: {
    'omegaup-arena': arena_Arena,
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-arena-summary': arena_Summary,
    'omegaup-problem-details': problem_Details,
  },
})
export default class ArenaCourse extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() currentAssignment!: types.ArenaAssignment;
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: null }) problem!: types.NavbarProblemsetProblem | null;
  @Prop() problemInfo!: types.ProblemInfo;
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];
  @Prop() activeTab!: string;
  @Prop({ default: null }) guid!: null | string;
  @Prop({ default: null }) problemAlias!: null | string;
  @Prop({ default: true }) socketConnected!: boolean;
  @Prop({ default: false }) showNewClarificationPopup!: boolean;
  @Prop({ default: () => [] }) runs!: types.Run[];

  T = T;
  currentClarifications = this.clarifications;
  activeProblem: types.NavbarProblemsetProblem | null = this.problem;
  shouldShowRunDetails = false;
  clock = '00:00:00';

  get activeProblemAlias(): null | string {
    return this.activeProblem?.alias ?? null;
  }

  get socketIcon(): string {
    if (this.socketConnected) return '•';
    return '✗';
  }

  get socketClass(): string {
    if (this.socketConnected) return 'socket-status-ok';
    return 'socket-status-error';
  }

  onNavigateToProblem(problem: types.NavbarProblemsetProblem) {
    this.activeProblem = problem;
    this.$emit('navigate-to-problem', { problem });
  }

  onRunSubmitted(run: { code: string; language: string }): void {
    this.$emit('submit-run', { ...run, ...{ problem: this.activeProblem } });
  }

  @Watch('problem')
  onActiveProblemChanged(newValue: types.NavbarProblemsetProblem | null): void {
    if (!newValue) {
      this.activeProblem = null;
      return;
    }
    this.onNavigateToProblem(newValue);
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
