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
          <select
            v-model="selectedStudent"
            class="ml-1 form-control"
            data-student
          >
            <option
              v-for="student in students"
              :key="student.username"
              :value="student.username"
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
              {{ assignment.name }}
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
                  @click="selectedProblem = problem"
                >
                  <template v-if="problem.runs.length > 0">
                    {{ bestScore(problem) * problem.points }} /
                    {{ problem.points }}
                  </template>
                  <template v-if="problem.is_extra_problem">
                    {{ T.studentProgressExtraProblem }}
                  </template>
                  <template
                    v-if="problem.runs.length > 0 || problem.is_extra_problem"
                    >-</template
                  >
                  {{ problem.title }} ({{ problem.runs.length }})
                </a>
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
              <pre class="m-0"><code>{{ selectedRunSource }}</code></pre>
            </div>
            <div class="card-body pb-0">
              <template v-if="selectedRun">
                <h5>{{ T.feedbackTitle }}</h5>
                <pre
                  class="border rounded rounded-lg p-2 m-0"
                  :class="{ 'bg-light': selectedRun.feedback == null }"
                  >{{
                    selectedRun.feedback
                      ? selectedRun.feedback.feedback
                      : T.feedbackNotSentYet
                  }}</pre
                >
                <div v-if="selectedRun.feedback && selectedRun.feedback.author">
                  {{
                    ui.formatString(T.feedbackLeftBy, {
                      date: time.formatDate(selectedRun.feedback.date),
                    })
                  }}
                  <omegaup-user-username
                    :username="selectedRun.feedback.author"
                    :classname="selectedRun.feedback.author_classname"
                    :linkify="true"
                  ></omegaup-user-username>
                </div>
                <div class="mt-3">
                  <a
                    class="btn btn-sm btn-primary"
                    role="button"
                    data-show-feedback-form
                    @click="showFeedbackForm = !showFeedbackForm"
                    >{{ T.submissionFeedbackSendButton }}</a
                  >
                  <div v-show="showFeedbackForm" class="form-group">
                    <p>{{ T.submissionFeedbackAnimationButton }}</p>
                    <img src="/media/submission_feedback_demo.gif" />
                  </div>
                </div>
              </template>
              <h5 class="card-title mt-3 mb-2">
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
                    :data-run-guid="run.guid"
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
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';

import omegaup_problemMarkdown from '../problem/ProblemMarkdown.vue';
import user_Username from '../user/Username.vue';

import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

@Component({
  components: {
    'omegaup-markdown': omegaup_problemMarkdown,
    'omegaup-user-username': user_Username,
  },
})
export default class CourseViewStudent extends Vue {
  @Prop() assignments!: omegaup.Assignment[];
  @Prop() course!: types.CourseDetails;
  @Prop() student!: types.StudentProgress;
  @Prop() assignment!: types.CourseAssignment;
  @Prop({ default: null }) problem!: null | types.CourseProblem;
  @Prop() problems!: types.CourseProblem[];
  @Prop() students!: types.StudentProgress[];
  @Prop({ default: null }) feedback!: string;

  T = T;
  time = time;
  ui = ui;
  selectedAssignment: string | null = this.assignment?.alias ?? null;
  selectedProblem: Partial<types.CourseProblem> | null = this.problem;
  selectedStudent: string | null = this.student?.username ?? null;
  selectedRun: Partial<types.CourseRun> | null = null;
  showFeedbackForm = false;
  updatedFeedback: null | string = this.feedback;

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
      if (this.selectedStudent !== null) {
        return;
      }
      this.selectedStudent = ev.state?.student ?? this.student.username;
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

  sendFeedback(): void {
    if (this.updatedFeedback != null && this.updatedFeedback.length < 2) {
      return;
    }
    this.$emit('set-feedback', {
      guid: this.selectedRun?.guid,
      feedback: this.updatedFeedback,
      isUpdate: this.selectedRun?.feedback != null,
      assignmentAlias: this.selectedAssignment,
      studentUsername: this.selectedStudent,
    });
    this.updatedFeedback = '';
    this.showFeedbackForm = false;
  }

  @Watch('selectedStudent')
  onSelectedStudentChange(newVal?: string, oldVal?: string) {
    this.$emit('update', {
      student: this.selectedStudent,
      assignmentAlias: this.selectedAssignment,
    });
    if (!newVal || newVal === oldVal) {
      return;
    }
    let url: string = '';
    if (this.selectedAssignment !== null) {
      url = `/course/${this.course.alias}/student/${newVal}/assignment/${this.selectedAssignment}/#${this.selectedProblem?.alias}`;
    } else {
      url = `/course/${this.course.alias}/student/${newVal}/`;
    }
    this.$emit('push-state', { student: newVal }, document.title, url);
  }

  @Watch('selectedAssignment')
  onSelectedAssignmentChange(newVal?: string, oldVal?: string) {
    this.$emit('update', {
      student: this.selectedStudent,
      assignmentAlias: this.selectedAssignment,
    });
    if (!newVal || newVal === oldVal) {
      return;
    }
    let url: string = '';
    if (this.selectedProblem !== null) {
      url = `/course/${this.course.alias}/student/${this.selectedStudent}/assignment/${newVal}/#${this.selectedProblem?.alias}`;
    } else {
      url = `/course/${this.course.alias}/student/${this.selectedStudent}/assignment/${newVal}/`;
    }
    this.$emit(
      'push-state',
      { student: this.selectedStudent },
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

  @Watch('selectedProblem')
  onSelectedProblemChange(newVal: types.CourseProblem) {
    this.selectedRun = newVal.runs?.[0] ?? null;
    window.location.hash = `#${this.selectedProblem?.alias}`;
  }

  @Watch('selectedRun')
  onSelectedRunChanged(newVal: Partial<types.CourseRun> | null = null) {
    if (newVal == null || newVal.feedback == null) {
      this.updatedFeedback = null;
      return;
    }
    this.updatedFeedback = newVal.feedback.feedback;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.student-runs-table tbody tr {
  cursor: pointer;
}
</style>
