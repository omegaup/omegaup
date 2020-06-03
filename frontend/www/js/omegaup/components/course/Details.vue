<template>
  <div>
    <h2 class="text-center">
      {{ course.name }}
      <a v-if="course.is_admin" v-bind:href="`/course/${course.alias}/edit/`">
        <font-awesome-icon v-bind:icon="['fas', 'edit']" />
      </a>
    </h2>
    <div class="my-4 markdown">
      <vue-mathjax
        v-bind:formula="descriptionHtml"
        v-bind:safe="false"
      ></vue-mathjax>
    </div>
    <div v-if="course.is_admin">
      {{
        ui.formatString(T.courseStudentCountLabel, {
          student_count: course.student_count,
        })
      }}
      <div class="mt-2">
        <a
          class="btn btn-primary"
          v-bind:href="`/course/${course.alias}/students/`"
          >{{ T.courseStudentsProgress }}</a
        >
        <a
          class="ml-2 btn btn-primary"
          v-bind:href="`/course/${course.alias}/edit/#students`"
          >{{ T.wordsAddStudent }}</a
        >
      </div>
    </div>
    <div class="mt-4 card">
      <h5 class="card-header">{{ T.wordsHomeworks }}</h5>
      <table class="table table-striped table-hover mb-0">
        <thead>
          <tr>
            <th class="text-center" scope="col">{{ T.wordsAssignment }}</th>
            <th class="text-center" scope="col">{{ T.wordsProgress }}</th>
            <th class="text-center" scope="col">{{ T.wordsStartTime }}</th>
            <th class="text-center" scope="col">{{ T.wordsEndTime }}</th>
            <th class="text-center" scope="col" v-if="course.is_admin">
              {{ T.courseDetailsScoreboard }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!filteredHomeworks.length">
            <td class="empty-table-message" colspan="5">
              {{ T.courseAssignmentEmpty }}
            </td>
          </tr>
          <tr v-else="" v-for="homework in filteredHomeworks">
            <td>
              <a
                class="text-center"
                v-bind:href="
                  `/course/${course.alias}/assignment/${homework.alias}/${
                    course.is_admin ? 'admin/' : ''
                  }`
                "
              >
                {{ homework.name }}
              </a>
            </td>
            <td class="text-center">
              {{ getAssignmentProgress(progress[homework.alias]) }}
            </td>
            <td class="text-center">
              {{ getFormattedTime(homework.start_time) }}
            </td>
            <td class="text-center">
              {{ getFormattedTime(homework.finish_time) }}
            </td>
            <td v-if="course.is_admin">
              <a
                class="glyphicon glyphicon-link"
                v-bind:href="
                  `/course/${course.alias}/assignment/${homework.alias}/scoreboard/${homework.scoreboard_url}`
                "
                >{{ T.wordsPublic }}</a
              >
              <a
                class="glyphicon glyphicon-link"
                v-bind:href="
                  `/course/${course.alias}/assignment/${homework.alias}/scoreboard/${homework.scoreboard_url_admin}`
                "
                >{{ T.wordsAdmin }}</a
              >
              <a
                class="glyphicon glyphicon-dashboard"
                v-bind:href="
                  `/course/${course.alias}/assignment/${homework.alias}/admin/#runs`
                "
                >{{ T.wordsRuns }}</a
              >
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="course">
      <div class="panel">
        <div class="panel-body table-responsive">
          <div>
            <h3>{{ T.wordsHomework }}</h3>
            <div class="container-fluid" v-if="course.is_admin">
              <div class="row">
                <a
                  class="btn btn-primary pull-right"
                  v-bind:href="
                    `/course/${course.alias}/edit/#assignments/new/homework/`
                  "
                  >{{ T.wordsNewHomework }}</a
                >
              </div>
            </div>

            <h3>{{ T.wordsExams }}</h3>
            <table class="assignments-list table table-striped table-hover">
              <thead>
                <tr>
                  <th>{{ T.wordsAssignment }}</th>
                  <th>{{ T.wordsProgress }}</th>
                  <th class="time">{{ T.wordsStartTime }}</th>
                  <th class="time">{{ T.wordsEndTime }}</th>
                  <th class="time" v-if="course.is_admin">
                    {{ T.courseDetailsScoreboard }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!filteredExams.length">
                  <td class="empty-category" colspan="5">
                    {{ T.courseExamEmpty }}
                  </td>
                </tr>
                <tr v-else="" v-for="exam in filteredExams">
                  <td>
                    <a
                      v-bind:href="
                        `/course/${course.alias}/assignment/${exam.alias}/${
                          course.is_admin ? 'admin/' : ''
                        }`
                      "
                    >
                      {{ exam.name }}
                    </a>
                  </td>
                  <td>{{ getAssignmentProgress(progress[exam.alias]) }}</td>
                  <td>{{ getFormattedTime(exam.start_time) }}</td>
                  <td>{{ getFormattedTime(exam.finish_time) }}</td>
                  <td v-if="course.is_admin">
                    <a
                      class="glyphicon glyphicon-link"
                      v-bind:href="
                        `/course/${course.alias}/assignment/${exam.alias}/scoreboard/${exam.scoreboard_url}`
                      "
                      >{{ T.wordsPublic }}</a
                    >
                    <a
                      class="glyphicon glyphicon-link"
                      v-bind:href="
                        `/course/${course.alias}/assignment/${exam.alias}/scoreboard/${exam.scoreboard_url_admin}`
                      "
                      >{{ T.wordsAdmin }}</a
                    >
                    <a
                      class="glyphicon glyphicon-dashboard"
                      v-bind:href="
                        `/course/${course.alias}/assignment/${exam.alias}/admin/#runs`
                      "
                      >{{ T.wordsRuns }}</a
                    >
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="container-fluid" v-if="course.is_admin">
              <div class="row">
                <a
                  class="btn btn-primary pull-right"
                  v-bind:href="
                    `/course/${course.alias}/edit/#assignments/new/test/`
                  "
                  >{{ T.wordsNewExam }}</a
                >
              </div>
            </div>
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
import * as markdown from '../../markdown';
import { types } from '../../api_types';

import { VueMathjax } from 'vue-mathjax';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEdit } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit);

@Component({
  components: {
    FontAwesomeIcon,
    'vue-mathjax': VueMathjax,
  },
})
export default class CourseDetails extends Vue {
  @Prop() course!: omegaup.Course;
  @Prop() progress!: types.AssignmentProgress[];

  T = T;
  ui = ui;
  markdownConverter = markdown.markdownConverter();

  get filteredHomeworks(): omegaup.Assignment[] {
    return this.course.assignments.filter(
      assignment => assignment.assignment_type === 'homework',
    );
  }

  get filteredExams(): omegaup.Assignment[] {
    return this.course.assignments.filter(
      assignment => assignment.assignment_type === 'test',
    );
  }

  getAssignmentProgress(progress: types.Progress): string {
    const percent = (progress.score / progress.max_score) * 100;
    const percentText = progress.max_score === 0 ? '--:--' : percent.toFixed(2);
    return progress.max_score === 0 ? percentText : `${percentText}%`;
  }

  getFormattedTime(timestamp: number): string {
    return time.formatDateTime(time.remoteTime(timestamp * 1000));
  }

  get descriptionHtml(): string {
    return this.markdownConverter.makeHtml(this.course.description);
  }
}
</script>
