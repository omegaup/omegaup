<template>
  <div class="omegaup-course-viewstudent card">
    <div class="card-header">
      <h2>
        <a :href="courseUrl">{{ course.name }}</a>
      </h2>
    </div>
    <div class="card-body">
      <form>
        <div class="form-group col-md-3">
          <label>{{ T.courseStudentSelectStudent }}</label>
          <select v-model="selectedStudent" class="ml-1 form-control">
            <option
              v-for="student in students"
              :key="student.username"
              :value="student"
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
            v-model="selectedAssignment"
            class="ml-1 form-control"
            data-assignment
          >
            <option
              v-for="assignment in assignments"
              :key="assignment.alias"
              :value="assignment.alias"
            >
              {{ assignment.name || selectedAssignmentAlias }}
            </option>
          </select>
        </div>
      </form>
      <div v-if="selectedAssignment">
        <omegaup-markdown
          :markdown="getAssignmentDescription(selectedAssignment)"
        ></omegaup-markdown>
        <hr />
        <div class="card">
          <div class="card-header">
            <template v-if="points(selectedAssignment) === 0">
              {{ T.studentProgressOnlyLecturesDescription }}
            </template>
            <ul v-else class="nav nav-pills card-header-pills">
              <li
                v-for="problem in problemsWithPoints"
                :key="problem.alias"
                class="nav-item"
                role="presentation"
              >
                <a
                  aria-controls="home"
                  data-toggle="tab"
                  href="#home"
                  class="nav-link"
                  :class="{
                    active:
                      selectedProblem &&
                      problem.alias === selectedProblem.alias,
                  }"
                  role="tab"
                  :data-problem-alias="problem.alias"
                  @click="selectProblem(problem)"
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
            <div class="empty-table-message px-10 py-10">
              {{ T.courseAssignmentProblemRunsEmpty }}
            </div>
          </div>
          <template v-else>
            <div class="card-body pb-0">
              <h5 class="card-title">
                {{ T.arenaCommonCode }}
              </h5>
              <pre>{{ selectedRunSource }}</pre>
            </div>
            <div class="card-body pb-0">
              <h5 class="card-title">
                {{ T.wordsSubmissions }}
              </h5>
              <table class="table table-hover student-runs-table">
                <thead>
                  <tr>
                    <th class="text-center">{{ T.wordsTime }}</th>
                    <th class="text-center">{{ T.wordsStatus }}</th>
                    <th class="numeric">{{ T.wordsPercentage }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="run in selectedProblem.runs"
                    :key="run.guid"
                    :class="{
                      'table-active':
                        selectedRun && run.guid === selectedRun.guid,
                    }"
                    @click="selectedRun = run"
                  >
                    <td class="text-center">
                      {{ time.formatDateTime(run.time) }}
                    </td>
                    <td class="text-center">{{ run.verdict }}</td>
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
  @Prop({default: null}) initialProblem!: types.CourseProblem | null;
  @Prop({default: null}) initialAssignment!: omegaup.Assignment | null;
  @Prop() problems!: types.CourseProblem[];
  @Prop() students!: types.StudentProgress[];

  T = T;
  time = time;
  selectedAssignment: string | null = this.initialAssignment?.alias??null;
  selectedProblem: Partial<types.CourseProblem> | null = this.initialProblem;
  selectedStudent: Partial<types.StudentProgress> = this.initialStudent || {};
  selectedRun: Partial<types.CourseRun> | null = null;

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

  mounted(): void {
    window.addEventListener('popstate', (ev: PopStateEvent) => {
      this.selectedStudent =
        (ev.state && ev.state.student) || this.initialStudent;
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

  get selectedRunSource(): string {
    return this.selectedRun?.source ?? '';
  }

  bestScore(problem: omegaup.CourseProblem): number {
    const best = this.bestRun(problem);
    return (best && best.score) || 0.0;
  }

  get courseUrl(): string {
    return `/course/${this.course.alias}/`;
  }

  selectProblem(selectedProblem: types.CourseProblem){
    this.selectedProblem = selectedProblem;
    window.location.hash = `#${selectedProblem.alias}`;
  }

  @Watch('selectedStudent')
  onSelectedStudentChange(
    newVal?: types.StudentProgress,
    oldVal?: types.StudentProgress,
  ) {
    let url: string = "";
    this.$emit('update', this.selectedStudent, this.selectedAssignment);
    if (!newVal || newVal?.username === oldVal?.username) {
      return;
    }
    if (this.selectedAssignment !== null) {
      url = `/course/${this.course.alias}/student/${newVal.username}/${this.selectedAssignment}/#${this.selectedProblem?.alias}`;
    }else{
      url = `/course/${this.course.alias}/student/${newVal.username}/`;
    }
    window.history.pushState(
      { student: newVal },
      document.title,
      url,
    );
  }

  @Watch('selectedAssignment')
  onSelectedAssignmentChange(newVal?: string, oldVal?: string) {
    let url: string = "";
    this.$emit('update', this.selectedStudent, this.selectedAssignment);
    if (!newVal || newVal === oldVal) {
      return;
    }
    if (this.selectedProblem !== null) {
      url = `/course/${this.course.alias}/student/${this.selectedStudent.username}/${newVal}/#${this.selectedProblem?.alias}`;
    }else{
      url = `/course/${this.course.alias}/student/${this.selectedStudent.username}/${newVal}/`;
    }
    window.history.pushState(
      { assignment: newVal },
      document.title,
      url,
    );
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
    this.selectedRun = found.runs?.[0] ?? null;
  }
}
</script>

<style scoped>
.student-runs-table tbody tr {
  cursor: pointer;
}
</style>
