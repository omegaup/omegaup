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
          :pager-items="course.clarificationsPagerItems"
          :page="course.clarificationsPage"
          :page-size="course.clarificationsPageSize"
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
import { defineComponent, PropType } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';
import { omegaup } from '../../omegaup';
import arena_Arena from './Arena.vue';
import arena_ClarificationList from './ClarificationList.vue';
import arena_NavbarAssignments from './NavbarAssignments.vue';
import arena_NavbarProblems from './NavbarProblems.vue';
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

const ArenaCourse = defineComponent({
  name: 'ArenaCourse',
  components: {
    'omegaup-arena': arena_Arena,
    'omegaup-arena-clarification-list': arena_ClarificationList,
    'omegaup-arena-navbar-assignments': arena_NavbarAssignments,
    'omegaup-arena-navbar-problems': arena_NavbarProblems,
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
  props: {
    course: {
      type: Object as PropType<types.CourseDetails>,
      required: true,
    },
    currentAssignment: {
      type: Object as PropType<types.ArenaAssignment>,
      required: true,
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
      type: (Object as unknown) as PropType<types.ProblemDetails | null>,
      default: null,
    },
    clarifications: {
      type: Array as PropType<types.Clarification[]>,
      default: () => [],
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
    socketStatus: {
      type: String as PropType<SocketStatus>,
      default: SocketStatus.Waiting,
    },
    showNewClarificationPopup: {
      type: Boolean,
      default: false,
    },
    scoreboard: {
      type: (Object as unknown) as PropType<null | types.Scoreboard>,
      default: null,
    },
    popupDisplayed: {
      type: (Number as unknown) as PropType<PopupDisplayed>,
      default: PopupDisplayed.None,
    },
    runs: {
      type: Array as PropType<types.Run[]>,
      default: () => [],
    },
    allRuns: {
      type: (Array as unknown) as PropType<null | types.Run[]>,
      default: null,
    },
    runDetailsData: {
      type: (Object as unknown) as PropType<types.RunDetails | null>,
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
    showRanking: {
      type: Boolean,
      default: false,
    },
    totalRuns: {
      type: Number,
      default: 0,
    },
    searchResultUsers: {
      type: Array as PropType<types.ListItem[]>,
      default: () => [],
    },
    feedbackMap: {
      type: (Map as unknown) as PropType<Map<number, ArenaCourseFeedback>>,
      default: () => new Map<number, ArenaCourseFeedback>(),
    },
    feedbackThreadMap: {
      type: (Map as unknown) as PropType<Map<number, ArenaCourseFeedback>>,
      default: () => new Map<number, ArenaCourseFeedback>(),
    },
    currentUsername: {
      type: String,
      default: '',
    },
    currentUserClassName: {
      type: String,
      default: '',
    },
  },
  emits: [
    'update:activeTab',
    'navigate-to-assignment',
    'request-feedback',
    'reset-hash',
    'submit-run',
    'execute-run',
    'show-run',
    'submit-promotion',
    'dismiss-promotion',
    'new-submission-popup-displayed',
    'submit-feedback',
    'set-feedback-status',
    'submit-feedback-thread',
    'new-clarification',
    'clarification-response',
    'navigate-to-problem',
  ],
  data() {
    return {
      T,
      omegaup,
      PopupDisplayed,
      currentClarifications: this.clarifications,
      activeProblem: this.problem as types.NavbarProblemsetProblem | null,
      currentRunDetailsData: this.runDetailsData as types.RunDetails | null,
      currentPopupDisplayed: this.popupDisplayed as PopupDisplayed,
      currentNextSubmissionTimestamp: this
        .nextSubmissionTimestamp as Date | null,
      currentNextExecutionTimestamp: this.nextExecutionTimestamp as Date | null,
      now: new Date(),
      INF: '∞',
    };
  },
  computed: {
    isAdmin(): boolean {
      return Boolean(
        this.course.is_admin ||
          this.course.is_curator ||
          this.course.is_teaching_assistant,
      );
    },
    activeProblemAlias(): null | string {
      return this.activeProblem?.alias ?? null;
    },
    socketClass(): string {
      if (this.socketStatus === SocketStatus.Connected) {
        return 'socket-status socket-status-ok';
      }
      if (this.socketStatus === SocketStatus.Failed) {
        return 'socket-status socket-status-error';
      }
      return 'socket-status';
    },
    socketStatusTitle(): string {
      if (this.socketStatus === SocketStatus.Connected) {
        return T.socketStatusConnected;
      }
      if (this.socketStatus === SocketStatus.Failed) {
        return T.socketStatusFailed;
      }
      return T.socketStatusWaiting;
    },
    deadline(): null | Date {
      return this.currentAssignment.finish_time ?? null;
    },
    problemDetailsPopup(): PopupDisplayed {
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
    },
    searchResultProblems(): types.ListItem[] {
      if (!this.problems.length) {
        return [];
      }
      return this.problems.map((problem) => ({
        key: problem.alias,
        value: problem.text,
      }));
    },
    language(): string | undefined {
      return this.runDetailsData?.language;
    },
    source(): string | undefined {
      return this.runDetailsData?.source;
    },
  },
  watch: {
    problem(newValue: types.NavbarProblemsetProblem | null): void {
      const currentProblem = this.currentAssignment.problems?.find(
        ({ alias }: { alias: string }) => alias === newValue?.alias,
      );
      if (!newValue || !currentProblem) {
        this.activeProblem = null;
        this.$emit('reset-hash', { selectedTab: 'problems', alias: null });
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
    runDetailsData(newValue: types.RunDetails): void {
      this.currentRunDetailsData = newValue;
    },
  },
  methods: {
    onPopupDismissed(): void {
      this.currentPopupDisplayed = PopupDisplayed.None;
      this.currentRunDetailsData = null;
      this.$emit('reset-hash', { selectedTab: 'runs', alias: null });
    },
    onNavigateToProblem(problem: types.NavbarProblemsetProblem): void {
      this.activeProblem = problem;
      this.$emit('navigate-to-problem', { problem });
    },
    onRunSubmitted(run: { code: string; language: string }): void {
      this.$emit('submit-run', { ...run, problem: this.activeProblem });
    },
    onRunAdminDetails(request: SubmissionRequest): void {
      this.$emit('show-run', {
        ...request,
        isAdmin: this.isAdmin,
        hash: `#runs/all/show-run:${request.guid}`,
      });
      this.currentPopupDisplayed = PopupDisplayed.RunDetails;
    },
    onRunDetails(request: SubmissionRequest): void {
      this.$emit('show-run', {
        ...request,
        hash: `#problems/${this.activeProblemAlias}/show-run:${request.guid}`,
      });
    },
    onRunExecuted(): void {
      this.$emit('execute-run', { target: this });
    },
  },
});

export default ArenaCourse;
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
