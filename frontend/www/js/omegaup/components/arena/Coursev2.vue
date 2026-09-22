<template>
  <div class="container-fluid p-5">
    <div>
      <a class="mb-2" :href="`/course/${course.alias}/`">
        <font-awesome-icon icon="chevron-left" />
        {{ T.arenaCourseAllContent }}
      </a>
      <h2 class="mb-0">{{ course.name }}</h2>
      <h4>{{ assignment.name }}</h4>
    </div>
    <div class="row px-3 mt-4 align-items-start">
      <div class="card col-md-3 col-lg-2 p-0 text-center">
        <nav class="card-header border-0">
          <ul class="nav nav-pills nav-justified">
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: currentSelectedTab === Tabs.Summary }"
                :href="`#${Tabs.Summary}`"
                @click="currentSelectedTab = Tabs.Summary"
                >{{ T.wordsSummary }}</a
              >
            </li>
            <li v-if="scoreboard" class="nav-item">
              <a
                class="nav-link"
                :class="{ active: currentSelectedTab === Tabs.Ranking }"
                :href="`#${Tabs.Ranking}`"
                @click="currentSelectedTab = Tabs.Ranking"
                >{{ T.wordsRanking }}</a
              >
            </li>
          </ul>
          <hr />
          <ul class="nav nav-pills flex-column">
            <li
              v-for="problem in problems"
              :key="problem.alias"
              class="nav-item"
            >
              <a
                class="nav-link"
                :href="`/course/${encodeURIComponent(
                  course.alias,
                )}/arena/${encodeURIComponent(
                  assignment.alias,
                )}/problem/${encodeURIComponent(problem.alias)}/`"
                :class="{
                  active:
                    !currentSelectedTab &&
                    currentProblem &&
                    currentProblem.alias === problem.alias,
                }"
                >{{
                  ui.formatString(T.arenaCourseProblemTitle, {
                    letter: problem.letter,
                    title: problem.title,
                  })
                }}</a
              >
            </li>
          </ul>
          <hr />
          <div>
            <a
              v-if="previousAssignment"
              class="btn btn-info btn-block"
              :href="`/course/${encodeURIComponent(
                course.alias,
              )}/arena/${encodeURIComponent(previousAssignment.alias)}/`"
            >
              <font-awesome-icon icon="arrow-circle-left" />
              {{ previousAssignment.name }}
            </a>
            <a
              v-if="nextAssignment"
              class="btn btn-info btn-block"
              :href="`/course/${encodeURIComponent(
                course.alias,
              )}/arena/${encodeURIComponent(nextAssignment.alias)}/`"
            >
              {{ nextAssignment.name }}
              <font-awesome-icon icon="arrow-circle-right" />
            </a>
          </div>
        </nav>
      </div>

      <div class="col-md-9 col-lg-10 mt-3 mt-md-0">
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
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import omegaup_Markdown from '../Markdown.vue';
import arena_Scoreboard from './Scoreboard.vue';
import problem_Details from '../problem/Detailsv2.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

export enum Tabs {
  Summary = 'summary',
  Ranking = 'ranking',
}

@Component({
  components: {
    FontAwesomeIcon,
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
