<template>
  <tr :class="studentProgress.username">
    <th scope="row" class="text-center align-middle">
      <a :href="studentProgressUrl">
        <omegaup-user-username
          :classname="studentProgress.classname"
          :username="studentProgress.username"
          :name="studentProgress.name"
          :country="studentProgress.country_id"
        ></omegaup-user-username>
      </a>
    </th>
    <td data-global-score class="text-center font-weight-bold align-middle">
      <span class="d-block"
        >{{ studentProgress.courseProgress.toFixed(0) }}%</span
      >
      <span class="d-block">{{
        ui.formatString(T.studentProgressPoints, {
          points: studentProgress.courseScore.toFixed(0),
        })
      }}</span>
    </td>
    <td
      v-for="assignment in assignmentsProblems"
      :key="assignment.alias"
      class="flex-column text-center align-middle text-nowrap justify-content-center align-items-center"
    >
      <span class="d-block">{{
        assignment.points === 0
          ? T.courseWithoutProblems
          : getProgressByAssignment(assignment.alias)
      }}</span>
      <span class="d-block">{{ getPointsByAssignment(assignment.alias) }}</span>
      <div class="d-flex justify-content-center mt-1">
        <div class="d-flex" :class="{ invisible: assignment.points === 0 }">
          <a
            v-for="problem in assignment.problems"
            :key="problem.alias"
            v-tooltip="getProgressTooltipDescription(assignment.alias, problem)"
            :class="getProblemColor(assignment.alias, problem)"
            data-toggle="tooltip"
            data-placement="bottom"
            :href="
              getStudentProgressUrlWithAssignmentAndProblem(
                assignment.alias,
                problem.alias,
              )
            "
          ></a>
        </div>
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
import user_Username from '../user/Username.vue';

@Component({
  directives: {
    tooltip: VTooltip,
  },
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class StudentProgress extends Vue {
  @Prop() courseAlias!: string;
  @Prop() studentProgress!: types.StudentProgressInCourse;
  @Prop() assignmentsProblems!: types.AssignmentsProblemsPoints[];

  T = T;
  ui = ui;

  getProgressByAssignment(assignmentAlias: string): string {
    const score =
      assignmentAlias in this.studentProgress.assignments
        ? this.studentProgress.assignments[assignmentAlias].progress
        : 0;
    return `${score.toFixed(0)}%`;
  }

  getPointsByAssignment(assignmentAlias: string): string {
    const score =
      assignmentAlias in this.studentProgress.assignments
        ? this.studentProgress.assignments[assignmentAlias].score
        : 0;
    return ui.formatString(T.studentProgressPoints, {
      points: score.toFixed(0),
    });
  }

  getProgressByAssignmentProblem(
    assignmentAlias: string,
    problemAlias: string,
  ): string {
    const score =
      assignmentAlias in this.studentProgress.assignments &&
      problemAlias in this.studentProgress.assignments[assignmentAlias].problems
        ? this.studentProgress.assignments[assignmentAlias].problems[
            problemAlias
          ].progress
        : 0;
    return score.toFixed(0);
  }

  getPointsByAssignmentProblem(
    assignmentAlias: string,
    problemAlias: string,
  ): string {
    const score =
      assignmentAlias in this.studentProgress.assignments &&
      problemAlias in this.studentProgress.assignments[assignmentAlias].problems
        ? this.studentProgress.assignments[assignmentAlias].problems[
            problemAlias
          ].progress
        : 0;
    return score.toFixed(0);
  }

  getProblemColor(
    assignmentAlias: string,
    problem: {
      alias: string;
      isExtraProblem: boolean;
      order: number;
      points: number;
      title: string;
    },
  ): string {
    if (problem.points === 0) {
      return 'invisible';
    }

    const problemProgress =
      assignmentAlias in this.studentProgress.assignments &&
      problem.alias in
        this.studentProgress.assignments[assignmentAlias].problems
        ? this.studentProgress.assignments[assignmentAlias].problems[
            problem.alias
          ].progress
        : 0;
    if (problemProgress > 70) return 'box bg-green';
    if (problemProgress >= 50) return 'box bg-yellow';
    if (problemProgress > 0) return 'box bg-red';
    return 'box bg-black';
  }

  getProgressTooltipDescription(
    assignmentAlias: string,
    problem: {
      alias: string;
      isExtraProblem: boolean;
      order: number;
      points: number;
      title: string;
    },
  ): string {
    return ui.formatString(T.studentProgressTooltipDescription, {
      problem: problem.title,
      score: this.getPointsByAssignmentProblem(assignmentAlias, problem.alias),
      progress: this.getProgressByAssignmentProblem(
        assignmentAlias,
        problem.alias,
      ),
      points: problem.points,
    });
  }

  get studentProgressUrl(): string {
    return `/course/${this.courseAlias}/student/${this.studentProgress.username}/`;
  }

  getStudentProgressUrlWithAssignmentAndProblem(
    selectedAssignment: string,
    selectedProblem: string,
  ): string {
    return `/course/${this.courseAlias}/student/${this.studentProgress.username}/assignment/${selectedAssignment}/#${selectedProblem}`;
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
