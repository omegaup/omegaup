<template>
  <omegaup-arena
    :active-tab="activeTab"
    :title="currentAssignment.name"
    :should-show-runs="isAdmin"
    :should-show-ranking="showRanking"
    @update:activeTab="(selectedTab) => $emit('update:activeTab', selectedTab)"
  >
    <template #socket-status>
      <sup :class="socketClass" :title="socketStatusTitle">{{
        socketStatus
      }}</sup>
    </template>
    <template #clock>
      <div v-if="!deadline" class="clock">{{ INF }}</div>
      <template v-else>
        <omegaup-countdown
          v-show="currentAssignment.start_time > now"
          :target-time="currentAssignment.start_time"
          :countdown-format="omegaup.CountdownFormat.AssignmentHasNotStarted"
          @finish="now = new Date()"
        ></omegaup-countdown>
        <omegaup-countdown
          v-show="currentAssignment.start_time < now"
          class="clock"
          :target-time="deadline"
          @finish="now = new Date()"
        ></omegaup-countdown>
      </template>
    </template>
    <template #arena-problems>
      <div data-course>
        <div class="tab navleft">
          <div class="navbar">
            <omegaup-arena-navbar-problems
              :problems="problems"
              :active-problem="activeProblemAlias"
              :in-assignment="true"
              :course-alias="course.alias"
              :course-name="course.name"
              :current-assignment="currentAssignment"
              :digits-after-decimal-point="2"
              @disable-active-problem="activeProblem = null"
              @navigate-to-problem="onNavigateToProblem"
            ></omegaup-arena-navbar-problems>
            <omegaup-arena-navbar-assignments
              :assignments="course.assignments"
              :current-assignment="currentAssignment"
              @navigate-to-assignment="
                (assignmentAliasToShow) =>
                  $emit('navigate-to-assignment', {
                    assignmentAliasToShow,
                    courseAlias: course.alias,
                  })
              "
            ></omegaup-arena-navbar-assignments>
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
              :next-submission-timestamp="currentNextSubmissionTimestamp"
              :next-execution-timestamp="currentNextExecutionTimestamp"
              :problem="problemInfo"
              :nomination-status="
                problemInfo ? problemInfo.nominationStatus : null
              "
              :popup-displayed="problemDetailsPopup"
              :request-feedback="true"
              :active-tab="'problems'"
              :languages="course.languages"
              :runs="runs"
              :guid="guid"
              :run-details-data="runDetailsData"
              :problem-alias="problemAlias"
              :in-contest-or-course="true"
              :feedback-map="feedbackMap"
              :feedback-thread-map="feedbackThreadMap"
              @request-feedback="(guid) => $emit('request-feedback', guid)"
              @update:activeTab="
                (selectedTab) =>
                  $emit('reset-hash', { selectedTab, problemAlias })
              "
              @submit-run="onRunSubmitted"
              @execute-run="onRunExecuted"
              @show-run="onRunDetails"
              @submit-promotion="
                (request) => $emit('submit-promotion', request)
              "
              @dismiss-promotion="
                (qualityPromotionComponent, isDismissed) =>
                  $emit('dismiss-promotion', {
                    solved: qualityPromotionComponent.solved,
                    tried: qualityPromotionComponent.tried,
                    isDismissed,
                  })
              "
              @new-submission-popup-displayed="
                $emit('new-submission-popup-displayed')
              "
            >
              <template #quality-nomination-buttons>
                <div></div>
              </template>
              <template #best-solvers-list>
                <div></div>
              </template>
              <template #feedback="{ guid, isAdmin, feedback }">
                <omegaup-submission-feedback
                  :guid="guid"
                  :is-admin="isAdmin"
                  :feedback-options="feedback"
                  @set-feedback="(request) => $emit('set-feedback', request)"
                ></omegaup-submission-feedback>
              </template>
            </omegaup-problem-details>
          </div>
        </div>
      </div>
    </template>
    <template v-if="scoreboard" #arena-scoreboard>
      <omegaup-arena-scoreboard
        :show-invited-users-filter="false"
        :problems="scoreboard.problems"
        :ranking="scoreboard.ranking"
        :last-updated="scoreboard.time"
      >
        <template #scoreboard-header><div></div></template>
      </omegaup-arena-scoreboard>
    </template>
    <template v-if="isAdmin" #arena-runs>
      <omegaup-arena-runs-for-courses
        :show-all-runs="true"
        :contest-alias="currentAssignment.alias"
        :runs="allRuns"
        :total-runs="totalRuns"
        :show-problem="true"
        :show-details="true"
        :show-disqualify="true"
        :show-filters="true"
        :show-rejudge="true"
        :show-user="true"
        :items-per-page="100"
        :problemset-problems="Object.values(problems)"
        :search-result-users="searchResultUsers"
        :search-result-problems="searchResultProblems"
        @details="onRunAdminDetails"
        @rejudge="(run) => $emit('rejudge', run)"
        @disqualify="(run) => $emit('disqualify', run)"
        @requalify="(run) => $emit('requalify', run)"
        @filter-changed="(request) => $emit('apply-filter', request)"
        @update-search-result-users-contest="
          (request) => $emit('update-search-result-users-assignment', request)
        "
      >
        <template #title><div></div></template>
        <template #runs><div></div></template>
      </omegaup-arena-runs-for-courses>
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
          >
            <template #feedback="{ guid, isAdmin, feedback }">
              <omegaup-submission-feedback
                :guid="guid"
                :is-admin="isAdmin"
                :feedback-options="feedback"
                @set-feedback="(request) => $emit('set-feedback', request)"
              ></omegaup-submission-feedback>
            </template>
            <template #code-view="{ guid }">
              <omegaup-arena-feedback-code-view
                :language="language"
                :value="source"
                :readonly="false"
                :feedback-map="feedbackMap"
                :feedback-thread-map="feedbackThreadMap"
                :current-user-class-name="currentUserClassName"
                :current-username="currentUsername"
                @save-feedback-list="
                  (feedbackList) =>
                    $emit('save-feedback-list', { feedbackList, guid })
                "
                @submit-feedback-thread="
                  (feedback) =>
                    $emit('submit-feedback-thread', { feedback, guid })
                "
              ></omegaup-arena-feedback-code-view>
            </template>
          </omegaup-arena-rundetails-popup>
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
import { omegaup } from '../../omegaup';
import arena_Arena from './Arena.vue';
import arena_ClarificationList from './ClarificationList.vue';
import arena_NavbarAssignments from './NavbarAssignments.vue';
import arena_NavbarProblems from './NavbarProblems.vue';
import arena_Runs from './Runs.vue';
import arena_RunsForCourses from '../arena/RunsForCourses.vue';
import arena_RunDetailsPopup from '../arena/RunDetailsPopup.vue';
import omegaup_Overlay from '../Overlay.vue';
import arena_Scoreboard from './Scoreboard.vue';
import arena_Summary from './Summary.vue';
import omegaup_Countdown from '../Countdown.vue';
import problem_Details, { PopupDisplayed } from '../problem/Details.vue';
import submission_Feedback from '../submissions/Feedback.vue';
import { SocketStatus } from '../../arena/events_socket';
import { SubmissionRequest } from '../../arena/submissions';
import arena_FeedbackCodeView from './FeedbackCodeView.vue';
import { ArenaCourseFeedback } from './Feedback.vue';

@Component({
  components: {
    'omegaup-arena': arena_Arena,
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena-navbar-assignments': arena_NavbarAssignments,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
    'omegaup-arena-runs': arena_Runs,
    'omegaup-arena-runs-for-courses': arena_RunsForCourses,
    'omegaup-arena-rundetails-popup': arena_RunDetailsPopup,
    'omegaup-overlay': omegaup_Overlay,
    'omegaup-arena-scoreboard': arena_Scoreboard,
    'omegaup-arena-summary': arena_Summary,
    'omegaup-problem-details': problem_Details,
    'omegaup-submission-feedback': submission_Feedback,
    'omegaup-countdown': omegaup_Countdown,
    'omegaup-arena-feedback-code-view': arena_FeedbackCodeView,
  },
})
export default class ArenaCourse extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() currentAssignment!: types.ArenaAssignment;
  @Prop() problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: null }) problem!: types.NavbarProblemsetProblem | null;
  @Prop({ default: null }) problemInfo!: types.ProblemDetails;
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];
  @Prop() activeTab!: string;
  @Prop({ default: null }) guid!: null | string;
  @Prop({ default: null }) problemAlias!: null | string;
  @Prop({ default: SocketStatus.Waiting }) socketStatus!: SocketStatus;
  @Prop({ default: false }) showNewClarificationPopup!: boolean;
  @Prop() scoreboard!: null | types.Scoreboard;
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop({ default: () => [] }) runs!: types.Run[];
  @Prop({ default: null }) allRuns!: null | types.Run[];
  @Prop({ default: null }) runDetailsData!: types.RunDetails | null;
  @Prop({ default: null }) nextSubmissionTimestamp!: Date | null;
  @Prop({ default: null }) nextExecutionTimestamp!: Date | null;
  @Prop({ default: false })
  shouldShowFirstAssociatedIdentityRunWarning!: boolean;
  @Prop({ default: false }) showRanking!: boolean;
  @Prop() totalRuns!: number;
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop({ default: () => new Map<number, ArenaCourseFeedback>() })
  feedbackMap!: Map<number, ArenaCourseFeedback>;
  @Prop({ default: () => new Map<number, ArenaCourseFeedback>() })
  feedbackThreadMap!: Map<number, ArenaCourseFeedback>;
  @Prop() currentUsername!: string;
  @Prop() currentUserClassName!: string;

  T = T;
  omegaup = omegaup;
  PopupDisplayed = PopupDisplayed;
  isAdmin =
    this.course.is_admin ||
    this.course.is_curator ||
    this.course.is_teaching_assistant;
  currentClarifications = this.clarifications;
  activeProblem: types.NavbarProblemsetProblem | null = this.problem;
  currentRunDetailsData = this.runDetailsData;
  currentPopupDisplayed = this.popupDisplayed;
  currentNextSubmissionTimestamp = this.nextSubmissionTimestamp;
  currentNextExecutionTimestamp = this.nextExecutionTimestamp;
  now = new Date();
  INF = '∞';

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

  get deadline(): null | Date {
    return this.currentAssignment.finish_time ?? null;
  }

  get problemDetailsPopup(): PopupDisplayed {
    if (!this.problemInfo) {
      return this.currentPopupDisplayed;
    }

    // Problem has not been solved or tried
    if (
      !this.problemInfo.nominationStatus.solved &&
      !this.problemInfo.nominationStatus.tried
    ) {
      return this.currentPopupDisplayed;
    }

    // Problem has been dismissed or has been dismissed beforeAC and has not been solved
    if (
      this.problemInfo.nominationStatus.dismissed ||
      (this.problemInfo.nominationStatus.dismissedBeforeAc &&
        !this.problemInfo.nominationStatus.solved)
    ) {
      return this.currentPopupDisplayed;
    }

    // Problem has been previously nominated
    if (
      this.problemInfo.nominationStatus.nominated ||
      (this.problemInfo.nominationStatus.nominatedBeforeAc &&
        !this.problemInfo.nominationStatus.solved)
    ) {
      return this.currentPopupDisplayed;
    }

    // User can't nominate the problem
    if (!this.problemInfo.nominationStatus.canNominateProblem) {
      return this.currentPopupDisplayed;
    }

    return PopupDisplayed.Promotion;
  }

  get searchResultProblems(): types.ListItem[] {
    if (!this.problems.length) {
      return [];
    }
    return this.problems.map((problem) => ({
      key: problem.alias,
      value: problem.text,
    }));
  }

  get language(): string | undefined {
    return this.runDetailsData?.language;
  }

  get source(): string | undefined {
    return this.runDetailsData?.source;
  }

  onPopupDismissed(): void {
    this.currentPopupDisplayed = PopupDisplayed.None;
    this.currentRunDetailsData = null;
    this.$emit('reset-hash', { selectedTab: 'runs', alias: null });
  }

  onNavigateToProblem(problem: types.NavbarProblemsetProblem) {
    this.activeProblem = problem;
    this.$emit('navigate-to-problem', { problem });
  }

  onRunSubmitted(run: { code: string; language: string }): void {
    this.$emit('submit-run', { ...run, problem: this.activeProblem });
  }

  onRunAdminDetails(request: SubmissionRequest): void {
    this.$emit('show-run', {
      ...request,
      isAdmin: this.isAdmin,
      hash: `#runs/all/show-run:${request.guid}`,
    });
    this.currentPopupDisplayed = PopupDisplayed.RunDetails;
  }

  onRunDetails(request: SubmissionRequest): void {
    this.$emit('show-run', {
      ...request,
      hash: `#problems/${this.activeProblemAlias}/show-run:${request.guid}`,
    });
  }

  onRunExecuted(): void {
    this.$emit('execute-run', { target: this });
  }

  @Watch('problem')
  onActiveProblemChanged(newValue: types.NavbarProblemsetProblem | null): void {
    const currentProblem = this.currentAssignment.problems?.find(
      ({ alias }: { alias: string }) => alias === newValue?.alias,
    );
    if (!newValue || !currentProblem) {
      this.activeProblem = null;
      this.$emit('reset-hash', { selectedTab: 'problems', alias: null });
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
