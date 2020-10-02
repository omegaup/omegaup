<template>
  <div>
    <h3 class="text-center">
      {{ course.name }}
      <a v-if="course.is_admin" v-bind:href="`/course/${course.alias}/edit/`">
        <font-awesome-icon v-bind:icon="['fas', 'edit']" />
      </a>
    </h3>
    <div class="my-4 markdown">
      <omegaup-markdown v-bind:markdown="course.description"></omegaup-markdown>
    </div>
    <div class="mb-5" v-if="course.is_admin">
      <span>{{
        ui.formatString(T.courseStudentCountLabel, {
          student_count: course.student_count,
        })
      }}</span>
      <div class="mt-2 row float-sm-right">
        <div class="col-sm">
          <div class="dropdown">
            <a
              data-button-statistics
              class="btn btn-primary dropdown-toggle"
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
                v-bind:href="`/course/${course.alias}/students/`"
                >{{ T.courseStudentsProgress }}</a
              >
              <a
                data-button-activity-report
                class="dropdown-item"
                v-bind:href="`/course/${course.alias}/activity/`"
                >{{ T.activityReport }}</a
              >
              <a
                data-button-activity-report
                class="dropdown-item"
                v-bind:href="`/course/${course.alias}/statistics/`"
                >{{ T.omegaupTitleCourseStatistics }}</a
              >
            </div>
          </div>
        </div>
        <div class="col-sm">
          <div class="dropdown">
            <a
              data-button-manage-course
              class="btn btn-primary dropdown-toggle"
              href="#"
              role="button"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              {{ T.courseEdit }}
            </a>
            <div class="dropdown-menu">
              <a
                data-button-manage-students
                class="dropdown-item"
                v-bind:href="`/course/${course.alias}/edit/#students`"
                >{{ T.wordsAddStudent }}</a
              >
              <a
                data-button-manage-content
                class="dropdown-item"
                v-bind:href="`/course/${course.alias}/edit/#content`"
                >{{ T.courseAddContent }}</a
              >
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="text-center align-middle" v-else>
      <span>
        {{ T.overallCompletedPercentage }}:
        <progress
          max="100"
          v-bind:value="overallCompletedPercentage"
          v-bind:title="`${overallCompletedPercentage} %`"
        ></progress>
      </span>
    </div>
    <div class="mt-4 card">
      <h5 class="card-header">{{ T.wordsContent }}</h5>
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead>
            <tr>
              <th class="text-center" scope="col">{{ T.wordsContentType }}</th>
              <th class="text-center" scope="col">{{ T.wordsName }}</th>
              <th class="text-center" scope="col" v-if="!course.is_admin">
                {{ T.wordsCompletedPercentage }}
              </th>
              <th class="text-center" scope="col">{{ T.wordsDueDate }}</th>
              <th class="text-center" scope="col" v-if="course.is_admin">
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
              v-else
              v-bind:key="assignment.alias"
              v-for="assignment in course.assignments"
              v-bind:data-content-alias="assignment.alias"
            >
              <td class="text-center">
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
                  class="text-center"
                  v-bind:href="`/course/${course.alias}/assignment/${
                    assignment.alias
                  }/${course.is_admin ? 'admin/' : ''}`"
                >
                  {{ assignment.name }}
                </a>
              </td>
              <td class="text-center" v-if="!course.is_admin">
                {{ getAssignmentProgress(progress[assignment.alias]) }}
              </td>
              <td class="text-center">
                {{ getFormattedTime(assignment.finish_time) }}
              </td>
              <td class="text-center" v-if="course.is_admin">
                <a
                  class="mr-2"
                  v-bind:href="`/course/${course.alias}/assignment/${assignment.alias}/scoreboard/${assignment.scoreboard_url}/`"
                >
                  <font-awesome-icon v-bind:icon="['fas', 'link']" />{{
                    T.wordsPublic
                  }}</a
                >
                <a
                  class="mr-2"
                  v-bind:href="`/course/${course.alias}/assignment/${assignment.alias}/edit/`"
                >
                  <font-awesome-icon v-bind:icon="['fas', 'edit']" />
                  {{ T.wordsEdit }}
                </a>
                <a
                  class="mr-2"
                  v-bind:href="`/course/${course.alias}/assignment/${assignment.alias}/admin/#runs`"
                >
                  <font-awesome-icon v-bind:icon="['fas', 'tachometer-alt']" />
                  {{ T.wordsRuns }}
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div
      class="accordion"
      data-accordion-clone
      v-if="course.admission_mode === 'public'"
    >
      <div class="card">
        <div class="card-header" data-heading-clone>
          <h2 class="mb-0">
            <button
              class="btn btn-link btn-block text-right"
              type="button"
              data-toggle="collapse"
              data-target="[data-accordion-collapse]"
              aria-expanded="false"
              aria-controls="data-accordion-collapse"
            >
              {{ T.wordsCloneThisCourse }}
            </button>
          </h2>
        </div>

        <div
          data-accordion-collapse
          class="collapse hide"
          aria-labelledby="[data-heading-clone]"
          data-parent="[data-accordion-clone]"
        >
          <div class="card-body">
            <omegaup-course-clone
              v-bind:initial-alias="course.alias"
              v-bind:initial-name="course.name"
              v-on:clone="
                (alias, name, startTime) =>
                  $emit('clone', alias, name, startTime)
              "
            ></omegaup-course-clone>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';
import { types } from '../../api_types';

import course_Clone from './Clone.vue';
import omegaup_Markdown from '../Markdown.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faChalkboardTeacher,
  faEdit,
  faFileAlt,
  faLink,
  faListAlt,
  faTachometerAlt,
} from '@fortawesome/free-solid-svg-icons';
library.add(
  faEdit,
  faLink,
  faTachometerAlt,
  faChalkboardTeacher,
  faFileAlt,
  faListAlt,
);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-course-clone': course_Clone,
  },
})
export default class CourseDetails extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() progress!: types.AssignmentProgress;

  T = T;
  ui = ui;

  get overallCompletedPercentage(): string {
    let score = 0;
    let maxScore = 0;
    for (const [assignment, progress] of Object.entries(this.progress)) {
      score += progress.score;
      maxScore += progress.max_score;
    }
    if (maxScore === 0) {
      return (0).toFixed(2);
    }
    const percent = (score / maxScore) * 100;

    return percent.toFixed(2);
  }

  getAssignmentProgress(progress: types.Progress): string {
    const percent = (progress.score / progress.max_score) * 100;
    const percentText = progress.max_score === 0 ? '--:--' : percent.toFixed(2);
    return progress.max_score === 0 ? percentText : `${percentText}%`;
  }

  getFormattedTime(date: Date | null | undefined): string {
    if (!date) {
      return '—';
    }
    return time.formatDateTime(date);
  }
}
</script>
