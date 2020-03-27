<template>
  <div>
    <div class="course">
      <div class="panel">
        <div class="panel-header">
          <div class="pull-right" v-if="course.is_admin">
            <a
              class="btn btn-primary"
              v-bind:href="`/course/${course.alias}/edit/`"
              >{{ T.wordsEditCourse }}</a
            >
          </div>
          <div class="">
            <a class="no-decoration">
              <h1>{{ course.name }}</h1>
            </a>
            <p class="container-fluid">{{ course.description }}</p>
          </div>
        </div>
        <div class="panel-body table-responsive">
          <div v-if="course.is_admin">
            <span>{{
              UI.formatString(T.courseStudentCountLabel, {
                student_count: course.student_count,
              })
            }}</span>
            <div class="pull-right">
              <a
                class="btn btn-primary"
                v-bind:href="`/course/${course.alias}/students/`"
                >{{ T.courseStudentsProgress }}</a
              >
              <a
                class="btn btn-primary"
                v-bind:href="`/course/${course.alias}/edit/#students`"
                >{{ T.wordsAddStudent }}</a
              >
            </div>
          </div>
          <div>
            <h3>{{ T.wordsHomework }}</h3>
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
                <tr v-for="homework in filteredHomeworks">
                  <td>
                    <a
                      v-bind:href="
                        `/course/${course.alias}/assignment/${homework.alias}`
                      "
                    >
                      {{ homework.name }}
                    </a>
                  </td>
                  <td>{{ getAssignmentProgress(progress[homework.alias]) }}</td>
                  <td>{{ getFormattedTime(homework.start_time) }}</td>
                  <td>{{ getFormattedTime(homework.finish_time) }}</td>
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
                <tr v-for="exam in filteredExams">
                  <td>
                    <a
                      v-bind:href="
                        `/course/${course.alias}/assignment/${exam.alias}`
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

<style>
.no-decoration {
  text-decoration: none;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { OmegaUp, omegaup, T } from '../../omegaup';
import UI from '../../ui.js';

@Component
export default class CourseDetails extends Vue {
  @Prop() course!: omegaup.Course;
  @Prop() progress!: omegaup.AssignmentProgress[];

  T = T;
  UI = UI;
  OmegaUp = OmegaUp;

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

  getAssignmentProgress(progress: omegaup.Progress): string {
    const percent = (progress.score / progress.max_score) * 100;
    const percentText = progress.max_score === 0 ? '--:--' : percent.toFixed(2);
    return progress.max_score === 0 ? percentText : `${percentText}%`;
  }

  getFormattedTime(timestamp: number): string {
    return UI.formatDateTime(OmegaUp.remoteTime(timestamp * 1000));
  }
}
</script>
