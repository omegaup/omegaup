<template>
  <div class="omegaup-course-viewstudent card">
    <div class="card-header">
      <h2>
        <a v-bind:href="courseUrl">{{ course.name }}</a>
      </h2>
    </div>
    <div class="card-body">
      <form>
        <select v-model="selectedStudent">
          <option v-bind:value="student" v-for="student in students">
            {{ student.name || student.username }}
          </option>
        </select>
      </form>
      <hr />
      <form>
        <select v-model="selectedAssignment">
          <option v-bind:value="assignment" v-for="assignment in assignments">
            {{ assignment.name }}
          </option>
        </select>
      </form>
      <div v-if="selectedAssignment">
        <p
          class="assignment-description"
          v-text="selectedAssignment.description"
        ></p>
        <hr />
        <div>
          <ul class="nav nav-tabs" role="tablist">
            <li
              role="presentation"
              v-bind:class="{ active: problem == selectedProblem }"
              v-for="problem in problems"
            >
              <a
                aria-controls="home"
                data-toggle="tab"
                href="#home"
                role="tab"
                v-bind:data-problem-alias="problem.alias"
                v-on:click="selectedProblem = problem"
              >
                <template v-if="problem.runs.length &gt; 0">
                  {{ bestScore(problem) * problem.points }} /
                  {{ problem.points }} - </template
                >{{ problem.title }} ({{ problem.runs.length }})</a
              >
            </li>
          </ul>
          <div v-if="!selectedProblem || selectedProblem.runs.length == 0">
            <div class="empty-category">
              {{ T.courseAssignmentProblemRunsEmpty }}
            </div>
          </div>
          <div class="card" v-else="">
            <div class="card-header">
              <pre>{{ bestRunSource(selectedProblem) }}</pre>
            </div>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>{{ T.wordsTime }}</th>
                  <th>{{ T.wordsStatus }}</th>
                  <th class="numeric">{{ T.wordsPercentage }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="run in selectedProblem.runs">
                  <td>{{ time.formatDateTime(run.time) }}</td>
                  <td>{{ run.verdict }}</td>
                  <td class="numeric">{{ 100 * run.score }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- panel-body -->
  </div>
  <!-- panel -->
</template>

<style>
.omegaup-course-viewstudent p.assignment-description {
  padding: 1em;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
@Component
export default class CourseViewStudent extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() course!: types.CourseDetails;
  @Prop() initialStudent!: types.StudentProgress;
  @Prop() problems!: omegaup.CourseProblem[];
  @Prop() students!: types.StudentProgress[];
  T = T;
  time = time;
  selectedAssignment: Partial<omegaup.Assignment> = {};
  selectedProblem?: Partial<omegaup.CourseProblem> = undefined;
  selectedStudent: Partial<types.StudentProgress> = this.initialStudent || {};
  data(): { [name: string]: any } {
    return {
      selectedProblem: undefined,
    };
  }
  mounted(): void {
    let self = this;
    window.addEventListener('popstate', function (ev: PopStateEvent): void {
      self.selectedStudent =
        (ev.state && ev.state.student) || self.initialStudent;
    });
  }
  bestRun(problem: omegaup.CourseProblem): omegaup.CourseProblemRun | null {
    let best = null;
    for (let run of problem.runs) {
      if (
        !best ||
        best.score < run.score ||
        (best.score == run.score && best.penalty > run.penalty)
      ) {
        best = run;
      }
    }
    return best;
  }
  bestRunSource(problem: omegaup.CourseProblem): string {
    const best = this.bestRun(problem);
    return (best && best.source) || '';
  }
  bestScore(problem: omegaup.CourseProblem): number {
    const best = this.bestRun(problem);
    return (best && best.score) || 0.0;
  }
  get courseUrl(): string {
    return `/course/${this.course.alias}/`;
  }
  @Watch('selectedStudent')
  onSelectedStudentChange(
    newVal?: types.StudentProgress,
    oldVal?: types.StudentProgress,
  ) {
    this.$emit('update', this.selectedStudent, this.selectedAssignment);
    if (!newVal || newVal?.username === oldVal?.username) {
      return;
    }
    window.history.pushState(
      { student: newVal },
      document.title,
      `/course/${this.course.alias}/student/${newVal.username}/`,
    );
  }
  @Watch('selectedAssignment')
  onSelectedAssignmentChange(newVal: omegaup.Assignment) {
    this.$emit('update', this.selectedStudent, this.selectedAssignment);
  }
  @Watch('problems')
  onProblemsChange(newVal: omegaup.CourseProblem[]) {
    if (newVal.length === 0) {
      this.selectedProblem = undefined;
      return;
    }
    this.selectedProblem = newVal[0];
  }
}
</script>
