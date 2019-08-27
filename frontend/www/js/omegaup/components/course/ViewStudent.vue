<template>
  <div class="omegaup-course-viewstudent panel">
    <div class="page-header">
      <h2><a v-bind:href="courseUrl">{{ course.name }}</a></h2>
    </div>
    <div class="panel-body">
      <form>
        <select v-model="selectedStudent">
          <option v-bind:value="student"
                  v-for="student in students">
            {{ student.name || student.username }}
          </option>
        </select>
      </form>
      <hr>
      <form>
        <select v-model="selectedAssignment">
          <option v-bind:value="assignment"
                  v-for="assignment in assignments">
            {{ assignment.name }}
          </option>
        </select>
      </form>
      <div v-if="selectedAssignment">
        <p class="assignment-description"
           v-text="selectedAssignment.description"></p>
        <hr>
        <div>
          <ul class="nav nav-tabs"
              role="tablist">
            <li role="presentation"
                v-bind:class="{ active: problem == selectedProblem }"
                v-for="problem in problems">
              <a aria-controls="home"
                  data-toggle="tab"
                  href="#home"
                  role="tab"
                  v-on:click="selectedProblem = problem">
              <template v-if="problem.runs.length &gt; 0">
                {{ bestScore(problem) * problem.points }} / {{ problem.points }} -
              </template>{{ problem.title }} ({{ problem.runs.length }})</a>
            </li>
          </ul>
          <div v-if="!selectedProblem || selectedProblem.runs.length == 0">
            <div class="empty-category">
              {{ T.courseAssignmentProblemRunsEmpty }}
            </div>
          </div>
          <div class="panel"
               v-else="">
            <div class="panel-header">
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
                  <td>{{ formatDateTime(run.time) }}</td>
                  <td>{{ run.verdict }}</td>
                  <td class="numeric">{{ 100 * run.score }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div><!-- panel-body -->
  </div><!-- panel -->
</template>

<style>
.omegaup-course-viewstudent p.assignment-description {
  padding: 1em;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class CourseViewStudent extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() course!: omegaup.Course;
  @Prop() initialStudent!: omegaup.CourseStudent;
  @Prop() problems!: omegaup.CourseProblem[];
  @Prop() students!: omegaup.CourseStudent[];

  T = T;
  UI = UI;
  selectedAssignment: Partial<omegaup.Assignment> = {};
  selectedProblem?: Partial<omegaup.CourseProblem> = undefined;
  selectedStudent: Partial<omegaup.CourseStudent> = this.initialStudent || {};

  data(): { [name: string]: any } {
    return {
      selectedProblem: undefined,
    };
  }

  mounted(): void {
    let self = this;
    window.addEventListener('popstate', function(ev: PopStateEvent): void {
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

  formatDateTime(date: Date): string {
    return UI.formatDateTime(date);
  }

  get courseUrl(): string {
    return `/course/${this.course.alias}/`;
  }

  @Watch('selectedStudent')
  onSelectedStudentChange(
    newVal: omegaup.CourseStudent,
    oldVal: omegaup.CourseStudent,
  ) {
    this.$emit('update', this.selectedStudent, this.selectedAssignment);
    if (newVal && oldVal && newVal.username == oldVal.username) {
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
