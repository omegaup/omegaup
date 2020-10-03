<template>
  <div class="omegaup-course-viewstudent card">
    <div class="card-header">
      <h2>
        <a v-bind:href="courseUrl">{{ course.name }}</a>
      </h2>
    </div>
    <div class="card-body">
      <form>
        <div class="form-group col-md-3">
          <label>{{ T.courseStudentSelectStudent }}</label>
          <select class="ml-1 form-control" v-model="selectedStudent">
            <option
              v-bind:value="student"
              v-for="student in students"
              v-bind:key="student.username"
            >
              {{ student.name || student.username }}
            </option>
          </select>
        </div>
      </form>
      <form>
        <div class="form-group col-md-3">
          <label>{{ T.courseStudentSelectAssignment }}</label>
          <select
            class="ml-1 form-control"
            v-model="selectedAssignment"
            data-assignment
          >
            <option
              v-bind:value="assignment.alias"
              v-for="assignment in assignments"
              v-bind:key="assignment.alias"
            >
              {{ assignment.name }}
            </option>
          </select>
        </div>
      </form>
      <div v-if="selectedAssignment">
        <omegaup-markdown
          v-bind:markdown="getAssignmentDescription(selectedAssignment)"
        ></omegaup-markdown>
        <hr />
        <div class="card">
          <div class="card-header">
            <template v-if="points(selectedAssignment) === 0">
              {{ T.studentProgressOnlyLecturesDescription }}
            </template>
            <ul class="nav nav-pills card-header-pills" v-else>
              <li
                class="nav-item"
                role="presentation"
                v-bind:class="{
                  active:
                    selectedProblem && problem.alias === selectedProblem.alias,
                }"
                v-for="problem in problemsWithPoints"
                v-bind:key="problem.alias"
              >
                <a
                  aria-controls="home"
                  data-toggle="tab"
                  href="#home"
                  class="nav-link"
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
          </div>
          <div v-if="!selectedProblem">
            <div class="empty-category px-10 py-10"></div>
          </div>
          <div v-else-if="selectedProblem.runs.length === 0">
            <div class="empty-category px-10 py-10">
              {{ T.courseAssignmentProblemRunsEmpty }}
            </div>
          </div>
          <template v-else>
            <div class="card-body pb-0">
              <h5 class="card-title">
                {{ T.arenaCommonCode }}
              </h5>
              <pre>{{ bestRunSource(selectedProblem) }}</pre>
            </div>
            <div class="card-body pb-0">
              <h5 class="card-title">
                {{ T.wordsSubmissions }}
              </h5>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>{{ T.wordsTime }}</th>
                    <th>{{ T.wordsStatus }}</th>
                    <th class="numeric">{{ T.wordsPercentage }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(run, index) in selectedProblem.runs"
                    v-bind:key="index"
                  >
                    <td>{{ time.formatDateTime(run.time) }}</td>
                    <td>{{ run.verdict }}</td>
                    <td class="numeric">{{ 100 * run.score }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>
        </div>
      </div>
    </div>
    <!-- card-body -->
  </div>
  <!-- card -->
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import omegaup_Markdown from '../Markdown.vue';
import T from '../../lang';
import * as time from '../../time';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseViewStudent extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() course!: types.CourseDetails;
  @Prop() initialStudent!: types.StudentProgress;
  @Prop() problems!: types.CourseProblem[];
  @Prop() students!: types.StudentProgress[];

  T = T;
  time = time;
  selectedAssignment: string | null = null;
  selectedProblem: Partial<types.CourseProblem> | null = null;
  selectedStudent: Partial<types.StudentProgress> = this.initialStudent || {};

  get problemsWithPoints(): types.CourseProblem[] {
    return this.problems.filter(
      (problem: types.CourseProblem) => problem.points !== 0,
    );
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  points(assignmentAlias: string): number {
    return this.problems.reduce(
      (accumulator: number, problem: types.CourseProblem) =>
        accumulator + problem.points,
      0,
    );
  }

  getAssignmentDescription(assignmentAlias: string): string {
    const assignment = this.assignments.find(
      (assignment) => assignment.alias === assignmentAlias,
    );
    return assignment?.description ?? '';
  }

  data(): { [name: string]: any } {
    return {
      selectedProblem: null,
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
  onSelectedAssignmentChange() {
    this.$emit('update', this.selectedStudent, this.selectedAssignment);
  }

  @Watch('problems')
  onProblemsChange(newVal: types.CourseProblem[]) {
    this.selectedProblem = null;
    if (newVal.length === 0) {
      return;
    }
    const found = newVal.find((problem) => problem.points !== 0);
    if (!found) {
      return;
    }
    this.selectedProblem = found;
  }
}
</script>
