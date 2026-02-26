<template>
  <b-container fluid class="p-5">
    <div>
      <a class="mb-2" :href="`/course/${course.alias}/`">
        <b-icon-chevron-left></b-icon-chevron-left>
        {{ T.arenaCourseAllContent }}
      </a>
      <h2 class="mb-0">{{ course.name }}</h2>
      <h4>{{ assignment.name }}</h4>
    </div>
    <b-row class="px-3 mt-4 align-items-start">
      <b-card no-body class="col-md-3 col-lg-2 p-0 text-center">
        <b-card-header header-tag="nav" class="border-0">
          <b-nav card-header pills justified>
            <b-nav-item
              :href="`#${Tabs.Summary}`"
              :active="currentSelectedTab === Tabs.Summary"
              @click="currentSelectedTab = Tabs.Summary"
              >{{ T.wordsSummary }}</b-nav-item
            >
            <b-nav-item
              v-if="scoreboard"
              :href="`#${Tabs.Ranking}`"
              :active="currentSelectedTab === Tabs.Ranking"
              @click="currentSelectedTab = Tabs.Ranking"
              >{{ T.wordsRanking }}</b-nav-item
            >
          </b-nav>
          <hr />
          <b-nav card-header pills vertical>
            <b-nav-item
              v-for="problem in problems"
              :key="problem.alias"
              :href="`/course/${encodeURIComponent(
                course.alias,
              )}/arena/${encodeURIComponent(
                assignment.alias,
              )}/problem/${encodeURIComponent(problem.alias)}/`"
              :active="
                !currentSelectedTab &&
                currentProblem &&
                currentProblem.alias === problem.alias
              "
              >{{
                ui.formatString(T.arenaCourseProblemTitle, {
                  letter: problem.letter,
                  title: problem.title,
                })
              }}</b-nav-item
            >
          </b-nav>
          <hr />
          <div>
            <b-button
              v-if="previousAssignment"
              block
              variant="info"
              :href="`/course/${encodeURIComponent(
                course.alias,
              )}/arena/${encodeURIComponent(previousAssignment.alias)}/`"
              ><b-icon-arrow-left-circle-fill></b-icon-arrow-left-circle-fill>
              {{ previousAssignment.name }}
            </b-button>
            <b-button
              v-if="nextAssignment"
              block
              variant="info"
              :href="`/course/${encodeURIComponent(
                course.alias,
              )}/arena/${encodeURIComponent(nextAssignment.alias)}/`"
              >{{ nextAssignment.name }}
              <b-icon-arrow-right-circle-fill></b-icon-arrow-right-circle-fill>
            </b-button>
          </div>
        </b-card-header>
      </b-card>

      <b-col md="9" lg="10" class="mt-3 mt-md-0">
        <omegaup-markdown
          v-if="currentSelectedTab === Tabs.Summary"
          :markdown="assignment.description"
          :full-width="true"
        ></omegaup-markdown>
        <omegaup-arena-scoreboard
          v-if="currentSelectedTab === Tabs.Ranking && scoreboard"
          :show-invited-users-filter="false"
          :problems="scoreboard.problems"
          :ranking="scoreboard.ranking"
          :last-updated="scoreboard.time"
        >
          <template #scoreboard-header>
            <h3 class="text-center">{{ T.wordsRanking }}</h3>
          </template>
        </omegaup-arena-scoreboard>
        <omegaup-problem-details
          v-if="currentSelectedTab === null && currentProblem"
          :all-runs="allRuns"
          :current-run-details="currentRunDetails"
          :in-contest-or-course="true"
          :languages="course.languages"
          :problem="currentProblem"
          :user="user"
          :user-runs="userRuns"
          @show-run-details="
            (request) => {
              $emit('show-run-details', request);
            }
          "
          @submit-run="
            (run) => {
              $emit('submit-run', run);
            }
          "
        ></omegaup-problem-details>
      </b-col>
    </b-row>
  </b-container>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import omegaup_Markdown from '../Markdown.vue';
import arena_Scoreboard from './Scoreboard.vue';
import problem_Details from '../problem/Detailsv2.vue';

import {
  BIconChevronLeft,
  BIconArrowLeftCircleFill,
  BIconArrowRightCircleFill,
  LayoutPlugin,
  CardPlugin,
  NavPlugin,
  ButtonPlugin,
} from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
Vue.use(LayoutPlugin);
Vue.use(CardPlugin);
Vue.use(NavPlugin);
Vue.use(ButtonPlugin);

export enum Tabs {
  Summary = 'summary',
  Ranking = 'ranking',
}

@Component({
  components: {
    BIconChevronLeft,
    BIconArrowLeftCircleFill,
    BIconArrowRightCircleFill,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-arena-scoreboard': arena_Scoreboard,
    'omegaup-problem-details': problem_Details,
  },
})
export default class ArenaCourse extends Vue {
  @Prop() allRuns!: types.Run[];
  @Prop() assignment!: types.ArenaCourseAssignment;
  @Prop() course!: types.ArenaCourseDetails;
  @Prop() currentProblem!: types.ProblemDetails;
  @Prop() currentRunDetails!: types.RunDetails | null;
  @Prop() problems!: types.ArenaCourseProblem[];
  @Prop() scoreboard!: types.Scoreboard;
  @Prop({ default: Tabs.Summary }) selectedTab!: string | null;
  @Prop() user!: types.UserInfoForProblem;
  @Prop() userRuns!: types.Run[];

  T = T;
  ui = ui;
  Tabs = Tabs;
  currentSelectedTab: string | null = this.selectedTab;

  private get currentAssignmentIndex(): number {
    return this.course.assignments.findIndex(
      (assignment) => assignment.alias === this.assignment.alias,
    );
  }

  get previousAssignment(): types.CourseAssignment | null {
    if (this.currentAssignmentIndex === 0) {
      return null;
    }
    return this.course.assignments[this.currentAssignmentIndex - 1];
  }

  get nextAssignment(): types.CourseAssignment | null {
    if (this.currentAssignmentIndex === this.course.assignments.length - 1) {
      return null;
    }
    return this.course.assignments[this.currentAssignmentIndex + 1];
  }

  @Watch('selectedTab')
  onSelectedTabChanged(newValue: string | null) {
    this.currentSelectedTab = newValue;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
