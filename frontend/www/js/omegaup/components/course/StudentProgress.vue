<template>
  <tr>
    <th scope="row" class="text-center align-middle">
      <a :href="studentProgressUrl">
        <omegaup-user-username
          :classname="student.classname"
          :username="student.username"
          :name="student.name"
          :country="student.country_id"
        ></omegaup-user-username>
      </a>
    </th>
    <td data-global-score class="text-center font-weight-bold align-middle">
      <span class="d-block">{{ globalScore }}%</span>
      <span class="d-block">{{
        ui.formatString(T.studentProgressDescriptionTotalPoints, {
          points: globalPoints,
        })
      }}</span>
    </td>
    <td
      v-for="assignment in assignments"
      :key="assignment.alias"
      class="flex-column text-center align-middle text-nowrap justify-content-center align-items-center"
    >
      <span class="d-block">{{
        getProgressByAssignment(assignment.alias)
      }}</span>
      <span class="d-block">{{
        getPointsByAsssignment(assignment.alias)
      }}</span>
      <div class="d-flex justify-content-center mt-1">
        <!-- Inicia barra de progreso -->
        <div
          v-if="
            Object.prototype.hasOwnProperty.call(
              student.progress,
              assignment.alias,
            )
          "
          class="d-flex"
          :class="{ invisible: points(assignment.alias) === 0 }"
        >
          <div
            v-for="(problem, index) in Object.keys(
              student.points[assignment.alias],
            )"
            :key="index"
            v-tooltip="getProgressTooltipDescription(assignment.alias, problem)"
            :class="getProblemColor(assignment.alias, problem)"
            data-toggle="tooltip"
            data-placement="bottom"
            @click="redirectToStudentProgress(assignment.alias, problem)"
          ></div>
        </div>
        <!-- Termina barra de progreso -->
      </div>
    </td>
  </tr>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';
import * as markdown from '../../markdown';
import user_Username from '../user/Username.vue';

const markdownConverter = new markdown.Converter();

@Component({
  directives: {
    tooltip: VTooltip,
  },
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class StudentProgress extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() student!: types.StudentProgress;
  @Prop() assignments!: types.CourseAssignment[];
  @Prop() problemTitles!: { [key: string]: string };
  @Prop() pagerItems!: types.PageItem[];

  T = T;
  ui = ui;

  progress(assignmentAlias: string): number {
    if (
      !Object.prototype.hasOwnProperty.call(
        this.student.progress,
        assignmentAlias,
      )
    ) {
      return 0;
    }
    return (
      (Object.values(this.student.progress[assignmentAlias]).reduce(
        (accumulator: number, currentValue: number) =>
          accumulator + currentValue,
        0,
      ) /
        (Object.values(this.student.progress[assignmentAlias]).length * 100)) *
      100
    );
  }

  score(assignmentAlias: string): number {
    if (
      !Object.prototype.hasOwnProperty.call(this.student.score, assignmentAlias)
    ) {
      return 0;
    }
    return Math.round(
      Object.values(this.student.score[assignmentAlias]).reduce(
        (accumulator: number, currentValue: number) =>
          accumulator + currentValue,
        0,
      ),
    );
  }

  points(assignmentAlias: string): number {
    if (
      !Object.prototype.hasOwnProperty.call(
        this.student.points,
        assignmentAlias,
      )
    ) {
      return 0;
    }
    return Object.values(this.student.points[assignmentAlias]).reduce(
      (accumulator: number, currentValue: number) => accumulator + currentValue,
      0,
    );
  }

  get totalPoints(): number {
    return this.assignments
      .map((assignment) => assignment.max_points ?? 0)
      .reduce((acc, curr) => acc + curr, 0);
  }

  get globalPoints(): string {
    if (!this.totalPoints) {
      return '0';
    }

    return this.assignments
      .map((assignment) => this.score(assignment.alias))
      .reduce((acc, curr) => acc + curr, 0)
      .toFixed(0);
  }

  get globalScore(): string {
    if (!this.totalPoints) {
      return '0.00';
    }

    return this.assignments
      .map(
        (assignment) => (this.score(assignment.alias) * 100) / this.totalPoints,
      )
      .reduce((acc, curr) => acc + curr, 0)
      .toFixed(0);
  }

  getProgressByAssignment(assignmentAlias: string): string {
    const score = this.score(assignmentAlias);
    const points = this.points(assignmentAlias);
    if (points === 0) {
      return T.courseWithoutProblems;
    }
    return (points != 0 ? (score / points) * 100 : 0).toFixed(0);
  }

  getPointsByAsssignment(assignmentAlias: string): string {
    const points = this.points(assignmentAlias);
    if (points === 0) {
      return '';
    }
    const score = this.score(assignmentAlias);
    return ui.formatString(T.studentProgressDescriptionTotalPoints, {
      points: score.toFixed(0),
    });
  }

  getProblemColor(assignmentAlias: string, problemAlias: string): string {
    const points = this.getPoints(assignmentAlias, problemAlias);
    if (points === 0) {
      return 'invisible';
    }
    const problemScore = this.getProgress(assignmentAlias, problemAlias);
    if (problemScore > 70) return 'box bg-green';
    if (problemScore >= 50) return 'box bg-yellow';
    if (problemScore > 0) return 'box bg-red';
    return 'box bg-black';
  }

  getProgress(assignmentAlias: string, problemAlias: string): number {
    return Math.round(this.student.progress[assignmentAlias][problemAlias]);
  }

  getPoints(assignmentAlias: string, problemAlias: string): number {
    return this.student.points[assignmentAlias][problemAlias];
  }

  getScore(assignmentAlias: string, problemAlias: string): number {
    return Math.round(this.student.score[assignmentAlias][problemAlias]);
  }

  getProgressTooltipDescription(
    assignmentAlias: string,
    problemAlias: string,
  ): string {
    return markdownConverter.makeHtml(
      ui.formatString(T.studentProgressTooltipDescription, {
        problem: this.problemTitles[problemAlias],
        score: this.getScore(assignmentAlias, problemAlias),
        points: this.getPoints(assignmentAlias, problemAlias),
        progress: this.getProgress(assignmentAlias, problemAlias),
      }),
    );
  }

  redirectToStudentProgress(assignmentAlias: string, problemAlias: string) {
    console.log(assignmentAlias + ' / ' + problemAlias);
    window.location.href = this.studentProgressUrl;
  }

  get studentProgressUrl(): string {
    return `/course/${this.course.alias}/student/${this.student.username}/`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.box {
  width: 20px;
  height: 20px;
  border: 1px solid $omegaup-dark-grey;
}

.bg-green {
  background: $omegaup-green;
}

.bg-yellow {
  background: yellow;
}

.bg-red {
  background: red;
}

.bg-black {
  background: $omegaup-grey;
}
</style>
