<template>
  <tr>
    <td class="text-center align-middle">
      <a v-bind:href="studentProgressUrl()">
        {{ student.name || student.username }}
      </a>
    </td>
    <td
      class="score flex-column justify-content-center align-items-center"
      v-for="assignment in assignments"
    >
      <p class="mb-1 text-center">{{ Math.round(score(assignment)) }}%</p>
      <div class="d-flex justify-content-center">
        <div v-if="student.progress.hasOwnProperty(assignment.alias) == false">
          {{ T.wordsProblemsUnsolved }}
        </div>
        <div
          v-for="problem in student.progress[assignment.alias]"
          v-bind:class="getProblemColor(Math.round(problem))"
          data-toggle="tooltip"
          data-placement="bottom"
          v-bind:title="problemScore(problem)"
        ></div>
      </div>
    </td>
  </tr>
</template>

<style>
.box {
  width: 20px;
  height: 20px;
  border: 1px solid black;
  margin: -0.5px;
}

.bg-green {
  background: rgb(53, 184, 53);
}

.bg-yellow {
  background: yellow;
}

.bg-red {
  background: red;
}

.bg-black {
  background: rgb(53, 53, 53);
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';

@Component
export default class StudentProgress extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() student!: types.StudentProgress;
  @Prop() assignments!: omegaup.Assignment[];

  T = T;

  problemScore(problem: number): string {
    return Math.round(problem) + '%';
  }

  score(assignment: omegaup.Assignment): number {
    if (!this.student.progress.hasOwnProperty(assignment.alias)) {
      return 0;
    }
    return (
      (Object.values(this.student.progress[assignment.alias]).reduce(
        (accumulator: number, currentValue: number) =>
          accumulator + currentValue,
        0,
      ) /
        (Object.values(this.student.progress[assignment.alias]).length * 100)) *
      100
    );
  }

  getProblemColor(problemScore: number): String {
    if (problemScore > 70) {
      return 'box bg-green';
    } else if (problemScore >= 50) {
      return 'box bg-yellow';
    } else if (problemScore > 0) {
      return 'box bg-red';
    } else {
      return 'box bg-black';
    }
  }

  studentProgressUrl(): string {
    return `/course/${this.course.alias}/student/${this.student.name}/`;
  }
}
</script>
