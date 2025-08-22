<template>
  <div>
    <a href="/course/">
      <font-awesome-icon :icon="['fas', 'chevron-left']" />
      {{ T.navAllCourses }}
    </a>
    <h3 class="text-center">
      <span :class="{ 'text-secondary': course.archived }">
        {{ course.name }}
      </span>
      <a v-if="course.is_admin" :href="`/course/${course.alias}/edit/`">
        <font-awesome-icon :icon="['fas', 'edit']" />
      </a>
    </h3>
    <div v-if="course.is_admin" class="d-flex justify-content-end mb-3">
      <button
        data-button-enable-ai-ta
        :class="[
          'btn p-1 p-sm-2 mr-2',
          isTogglingTA ? 'btn-light' : 'btn-primary',
        ]"
        type="button"
        :disabled="isTogglingTA"
        @click="toggleTeachingAssistant"
      >
        {{ teachingAssistantEnabled ? T.wordsDisableAITA : T.wordsEnableAITA }}
      </button>
      <button
        data-button-run-ai-ta
        class="btn btn-primary p-1 p-sm-2"
        type="button"
      >
        {{ T.wordsRunAITA }}
      </button>
    </div>
    <div v-if="isAdminOrTeachingAssistant" class="my-5">
      <div class="my-4 markdown">
        <omegaup-markdown
          :markdown="course.description"
          :full-width="true"
        ></omegaup-markdown>
      </div>
      <span>{{
        ui.formatString(T.courseStudentCountLabel, {
          student_count: course.student_count,
        })
      }}</span>
      <div class="mt-2 row float-sm-right">
        <div v-if="course.is_admin" class="col">
          <div class="dropdown">
            <a
              data-button-statistics
              class="btn btn-primary dropdown-toggle p-1 p-sm-2"
              href="#"
              role="button"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              {{ T.wordsStatistics }}
            </a>
            <div class="dropdown-menu">
              <a
                data-button-progress-students
                class="dropdown-item"
                :href="`/course/${course.alias}/students/`"
                >{{ T.courseStudentsProgress }}</a
              >
              <a
                data-button-activity-report
                class="dropdown-item"
                :href="`/course/${course.alias}/activity/`"
                >{{ T.activityReport }}</a
              >
              <a
                data-button-activity-report
                class="dropdown-item"
                :href="`/course/${course.alias}/statistics/`"
                >{{ T.omegaupTitleCourseStatistics }}</a
              >
            </div>
          </div>
        </div>
        <div v-if="course.is_admin" class="col d-flex justify-content-center">
          <div class="dropdown">
            <a
              data-button-manage-course
              class="btn btn-primary dropdown-toggle p-1 p-sm-2"
              href="#"
              role="button"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              {{ T.courseDetailsSettings }}
            </a>
            <div class="dropdown-menu">
              <a
                data-button-manage-students
                class="dropdown-item"
                :href="`/course/${course.alias}/edit/#students`"
                >{{ T.wordsAddStudent }}</a
              >
              <a
                data-button-manage-content
                class="dropdown-item"
                :href="`/course/${course.alias}/edit/#content`"
                >{{ T.wordsContentEdit }}</a
              >
              <a
                data-button-manage-content
                class="dropdown-item"
                :href="`/course/${course.alias}/edit/#clone`"
                >{{ T.wordsCloneThisCourse }}</a
              >
            </div>
          </div>
        </div>
        <div class="col">
          <a
            :href="`/course/${course.alias}/clarification/`"
            role="button"
            class="btn btn-primary p-1 p-sm-2"
            >{{ T.wordsClarifications }}</a
          >
        </div>
      </div>
      <div class="card mt-5">
        <h5 class="card-header">{{ T.wordsContent }}</h5>
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr class="text-center">
                <th class="align-middle" scope="col">
                  {{ T.wordsContentType }}
                </th>
                <th class="align-middle" scope="col">{{ T.wordsName }}</th>
                <th
                  v-if="!isAdminOrTeachingAssistant"
                  class="align-middle"
                  scope="col"
                >
                  {{ T.wordsCompletedPercentage }}
                </th>
                <th class="align-middle" scope="col">{{ T.wordsDueDate }}</th>
                <th
                  v-if="isAdminOrTeachingAssistant"
                  class="align-middle"
                  scope="col"
                >
                  {{ T.wordsActions }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!course.assignments.length">
                <td class="empty-table-message" colspan="5">
                  {{ T.courseContentEmpty }}
                </td>
              </tr>
                              <tr
                  v-for="assignment in course.assignments"
                  v-else
                  :key="assignment.alias"
                  :data-content-alias="assignment.alias"
                  class="text-center"
                >
                  <td class="align-middle">
                    <template v-if="assignment.assignment_type === 'homework'">
                      <font-awesome-icon icon="file-alt" />
                      <span class="ml-2">{{ T.wordsHomework }}</span>
                    </template>
                    <template v-else-if="assignment.assignment_type === 'lesson'">
                      <font-awesome-icon icon="chalkboard-teacher" />
                      <span class="ml-2">{{ T.wordsLesson }}</span>
                    </template>
                    <template v-else>
                      <font-awesome-icon icon="list-alt" />
                      <span class="ml-2">{{ T.wordsExam }}</span>
                    </template>
                  </td>
                  <td>
                    <a
                      data-course-homework-button
                      class="align-middle"
                      :href="`/course/${course.alias}/assignment/${assignment.alias}/`"
                    >
                      {{ assignment.name }}
                    </a>
                  </td>
                  <td v-if="!isAdminOrTeachingAssistant" class="align-middle">
                    {{ getAssignmentProgress(progress[assignment.alias]) }}
                  </td>
                  <td class="align-middle">
                    {{ getFormattedTime(assignment.finish_time) }}
                  </td>
                  <td v-if="isAdminOrTeachingAssistant" class="align-middle">
                    <a
                      data-course-scoreboard-button
                      class="mr-2"
                      :href="`/course/${course.alias}/assignment/${assignment.alias}/scoreboard/${assignment.scoreboard_url}/`"
                    >
                      <font-awesome-icon :icon="['fas', 'link']" />{{
                        T.courseActionScoreboard
                      }}</a
                    >
                    <a
                      data-course-submisson-button
                      class="mr-2"
                      :href="`/course/${course.alias}/assignment/${assignment.alias}/#runs`"
                    >
                      <font-awesome-icon :icon="['fas', 'tachometer-alt']" />
                      {{ T.wordsRuns }}
                    </a>
                  </td>
                </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <template v-else>
      <div class="mt-4 mb-1">
        <div class="progress w-50 mx-auto">
          <div
            class="progress-bar text-dark"
            role="progressbar"
            :aria-valuenow="overallCompletedPercentage"
            aria-valuemin="0"
            aria-valuemax="100"
            :style="`width: ${overallCompletedPercentage}%`"
          >
            {{ overallCompletedPercentage.toFixed(0) }}%
          </div>
        </div>
        <div
          class="w-50 mx-auto d-flex justify-content-between text-center progress-text"
        >
          <p class="my-0 text-uppercase">
            {{ T.courseDetailsProgress }}
          </p>
          <p class="my-0">
            {{ overallCompletedPoints }}
          </p>
        </div>
      </div>
      <div class="d-flex justify-content-end py-3 px-5 mx-3 p-sm-0 mx-sm-0">
        <div class="dropdown">
          <a
            class="btn btn-primary dropdown-toggle"
            href="#"
            role="button"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            {{ T.wordsActions }}
          </a>
          <div class="dropdown-menu">
            <a
              :href="`/course/${course.alias}/clarification/`"
              class="dropdown-item"
              >{{ T.wordsMyClarifications }}</a
            >
            <a
              v-if="course.admission_mode === 'public'"
              :href="`/course/${course.alias}/clone/`"
              class="dropdown-item"
              >{{ T.wordsCloneThisCourse }}</a
            >
          </div>
        </div>
      </div>
      <ul class="nav nav-tabs" role="tablist">
        <li
          v-for="(tabName, tabKey) in tabNames"
          :key="tabKey"
          class="nav-item"
          role="presentation"
        >
          <a
            class="nav-link"
            :href="`#${tabKey}`"
            :class="{ active: selectedTab === tabKey }"
            data-toggle="tab"
            role="tab"
            @click="selectedTab = tabKey"
            >{{ tabName }}</a
          >
        </li>
      </ul>
      <div class="tab-content">
        <div
          class="tab-pane fade py-4 px-2"
          :data-tab="tabNames.information"
          :class="{
            show: selectedTab === Tab.Information,
            active: selectedTab === Tab.Information,
          }"
          role="tabpanel"
        >
          <omegaup-markdown
            :markdown="course.description"
            :full-width="true"
          ></omegaup-markdown>
          <div class="row m-0 mt-4">
            <div v-if="course.objective" class="col-md-8 mb-4 p-0 pr-md-5">
              <h5 class="intro-subtitle pb-1">
                {{ T.courseNewFormObjective }}
              </h5>
              <omegaup-markdown
                :markdown="course.objective"
                :full-width="true"
              ></omegaup-markdown>
            </div>
            <div
              v-if="course.school_id && course.school_name"
              class="col-md-4 p-0"
            >
              <h5 class="intro-subtitle pb-1 mb-2">
                {{ T.courseIntroImpartedBy }}
              </h5>
              {{ course.school_name }}
            </div>
          </div>
        </div>
        <div
          class="tab-pane fade py-4 px-2"
          :class="{
            show: selectedTab === Tab.Content,
            active: selectedTab === Tab.Content,
          }"
          role="tabpanel"
        >
          <omegaup-assignment-card
            v-for="assignment in course.assignments"
            :key="assignment.alias"
            :assignment="assignment"
            :course-alias="course.alias"
            :student-progress="
              getAssignmentProgress(progress[assignment.alias])
            "
          ></omegaup-assignment-card>
        </div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';
import { types } from '../../api_types';

import omegaup_Markdown from '../Markdown.vue';
import course_AssignmentCard from './AssignmentCard.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEdit,
  faLink,
  faTachometerAlt,
  faChevronLeft,
} from '@fortawesome/free-solid-svg-icons';
library.add(faEdit, faLink, faTachometerAlt, faChevronLeft);

export enum Tab {
  Information = 'information',
  Content = 'content',
}

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-assignment-card': course_AssignmentCard,
  },
})
export default class CourseDetails extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() progress!: types.AssignmentProgress;
  @Prop() currentUsername!: string;

  T = T;
  ui = ui;
  Tab = Tab;
  tabNames: Record<Tab, string> = {
    [Tab.Information]: T.courseDetailsTabInformation,
    [Tab.Content]: T.courseDetailsTabContent,
  };
  selectedTab = Tab.Content;
  teachingAssistantEnabled = false;
  isTogglingTA = false;

  mounted() {
    this.teachingAssistantEnabled =
      this.course.teaching_assistant_enabled || false;
  }

  get overallCompletedPercentage(): number {
    let score = 0;
    let maxScore = 0;
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    for (const [assignment, progress] of Object.entries(this.progress)) {
      score += progress.score;
      maxScore += progress.max_score;
    }
    if (maxScore === 0) {
      return 0;
    }
    return (score / maxScore) * 100;
  }

  get overallCompletedPoints(): string {
    let score = 0;
    let maxScore = 0;
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    for (const [assignment, progress] of Object.entries(this.progress)) {
      score += progress.score;
      maxScore += progress.max_score;
    }
    return ui.formatString(T.courseDetailsOverallCompletedPoints, {
      completed_points: score,
      total_points: maxScore,
    });
  }

  get isAdminOrTeachingAssistant(): boolean {
    return this.course.is_admin || this.course.is_teaching_assistant;
  }

  getAssignmentProgress(progress: types.Progress): number {
    return progress.max_score === 0
      ? 100
      : (progress.score / progress.max_score) * 100;
  }

  getFormattedTime(date: Date | null | undefined): string {
    if (!date) {
      return '—';
    }
    return time.formatDateTime(date);
  }

  get aliasWithUsername(): string {
    return `${this.course.alias}_${this.currentUsername}`;
  }

  toggleTeachingAssistant(): void {
    if (this.isTogglingTA) return;
    this.isTogglingTA = true;
    this.$emit('toggle-teaching-assistant', {
      course_alias: this.course.alias,
    });
  }

  updateTeachingAssistantStatus(enabled: boolean): void {
    this.teachingAssistantEnabled = enabled;
    this.$set(this.course, 'teaching_assistant_enabled', enabled);
    this.isTogglingTA = false;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.progress-text {
  font-size: 0.85rem;
}

.progress-bar {
  background-color: $omegaup-yellow;
}

h5.intro-subtitle {
  color: $omegaup-grey;
  width: 20rem;
  border-bottom: 4px solid $omegaup-primary--accent;
}
</style>
