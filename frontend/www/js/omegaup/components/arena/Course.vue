<template>
  <omegaup-arena
    :active-tab="activeTab"
    :contest-title="currentAssignment.name"
    :should-show-runs="isAdmin"
    @update:activeTab="(selectedTab) => $emit('update:activeTab', selectedTab)"
  >
    <template #socket-status>
      <sup :class="socketClass" :title="socketStatusTitle">{{
        socketStatus
      }}</sup>
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
              ref="problem-details"
              :user="{ loggedIn: true, admin: false, reviewer: false }"
              :problem="problemInfo"
              :active-tab="'problems'"
              :runs="runs"
              :guid="guid"
              :popup-displayed="popupDisplayed"
              :problem-alias="problemAlias"
              @update:activeTab="
                (selectedTab) =>
                  $emit('reset-hash', { selectedTab, problemAlias })
              "
              @submit-run="onRunSubmitted"
              @show-run="onRunDetails"
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
    <template #arena-scoreboard>
      <omegaup-arena-scoreboard
        :show-invited-users-filter="false"
        :problems="scoreboard.problems"
        :ranking="scoreboard.ranking"
        :last-updated="scoreboard.time"
      ></omegaup-arena-scoreboard>
    </template>
    <template #arena-runs>
      <omegaup-arena-runs
        v-if="isAdmin"
        :contest-alias="currentAssignment.alias"
        :runs="allRuns"
        :show-problem="true"
        :show-details="true"
        :show-disqualify="true"
        :show-pager="true"
        :show-rejudge="true"
        :show-user="true"
        :problemset-problems="Object.values(problems)"
        :global-runs="false"
        @details="(run) => onRunDetails(run.guid)"
        @rejudge="(run) => $emit('rejudge', run)"
        @disqualify="(run) => $emit('disqualify', run)"
      ></omegaup-arena-runs>
      <omegaup-overlay
        v-if="isAdmin"
        :show-overlay="currentPopupDisplayed !== PopupDisplayed.None"
        @hide-overlay="onPopupDismissed"
      >
        <template #popup>
          <omegaup-arena-rundetails-popup
            v-show="currentPopupDisplayed === PopupDisplayed.RunDetails"
            :data="currentRunDetailsData"
            @dismiss="onPopupDismissed"
          ></omegaup-arena-rundetails-popup>
        </template>
      </omegaup-overlay>
    </template>
    <template #arena-clarifications>
      <div class="container">
        <omegaup-arena-clarification-list
          :problems="problems"
          :users="users"
          :problem-alias="problems.length != 0 ? problems[0].alias : null"
          :username="isAdmin && users.length != 0 ? users[0].username : null"
          :clarifications="currentClarifications"
          :is-admin="isAdmin"
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
import arena_Runs from './Runsv2.vue';
import arena_RunDetailsPopup from '../arena/RunDetailsPopup.vue';
import omegaup_Overlay from '../Overlay.vue';
import arena_Scoreboard from './Scoreboard.vue';
import arena_Summary from './Summary.vue';
import problem_Details, { PopupDisplayed } from '../problem/Details.vue';
import { SocketStatus } from '../../arena/events_socket';
import { SubmissionRequest } from '../../arena/submissions';

@Component({
  components: {
    'omegaup-arena': arena_Arena,
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-arena-runs': arena_Runs,
    'omegaup-arena-rundetails-popup': arena_RunDetailsPopup,
    'omegaup-overlay': omegaup_Overlay,
    'omegaup-arena-scoreboard': arena_Scoreboard,
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
  @Prop({ default: SocketStatus.Waiting }) socketStatus!: SocketStatus;
  @Prop({ default: false }) showNewClarificationPopup!: boolean;
  @Prop() scoreboard!: types.Scoreboard;
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop({ default: () => [] }) runs!: types.Run[];
  @Prop({ default: false }) shouldShowRunDetails!: boolean;
  @Prop({ default: null }) allRuns!: null | types.Run[];
  @Prop({ default: null }) runDetailsData!: types.RunDetails | null;

  T = T;
  PopupDisplayed = PopupDisplayed;
  isAdmin = this.course.is_admin || this.course.is_curator;
  currentClarifications = this.clarifications;
  activeProblem: types.NavbarProblemsetProblem | null = this.problem;
  currentRunDetailsData = this.runDetailsData;
  currentPopupDisplayed = this.popupDisplayed;
  clock = '00:00:00';

  get activeProblemAlias(): null | string {
    return this.activeProblem?.alias ?? null;
  }

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

  onPopupDismissed(): void {
    this.currentPopupDisplayed = PopupDisplayed.None;
    this.$emit('reset-hash', { selectedTab: 'runs', alias: null });
  }

  onNavigateToProblem(problem: types.NavbarProblemsetProblem) {
    this.activeProblem = problem;
    this.$emit('navigate-to-problem', { problem });
  }

  onRunSubmitted(run: { code: string; language: string }): void {
    this.$emit('submit-run', { ...run, problem: this.activeProblem });
  }

  onRunDetails(source: SubmissionRequest): void {
    this.$emit('show-run', {
      ...source,
      request: {
        ...source.request,
        hash: `#problems/${
          this.activeProblemAlias ?? source.request.problemAlias
        }/show-run:${source.request.guid}/`,
      },
    });
  }

  @Watch('problem')
  onActiveProblemChanged(newValue: types.NavbarProblemsetProblem | null): void {
    if (!newValue) {
      this.activeProblem = null;
      return;
    }
    this.onNavigateToProblem(newValue);
  }

  @Watch('shouldShowRunDetails')
  onShouldShowRunDetailsChanged(newValue: boolean): void {
    if (!newValue || !this.guid) {
      return;
    }
    this.$nextTick(() => {
      const problemDetails = this.$refs['problem-details'] as problem_Details;
      this.$emit('show-run', {
        request: {
          guid: this.guid,
          hash: `#problems/show-run:${this.guid}/`,
          isAdmin: this.course.is_admin,
          problemAlias: this.activeProblemAlias,
        },
        target: problemDetails,
      });
    });
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
