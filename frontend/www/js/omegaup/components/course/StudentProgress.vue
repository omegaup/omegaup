<template>
  <tr>
    <td class="text-center align-middle">
      <a v-bind:href="studentProgressUrl">
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
        <div v-else class="d-flex border border-dark">
          <div
            v-for="problemScore in student.progress[assignment.alias]"
            v-bind:class="getProblemColor(Math.round(problemScore))"
            data-toggle="tooltip"
            data-placement="bottom"
            v-bind:title="`${Math.round(problemScore)}%`"
          ></div>
        </div>
      </div>
    </td>
  </tr>
</template>

<style lang="scss">
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
    if (problemScore > 70) return 'box bg-green';
    if (problemScore >= 50) return 'box bg-yellow';
    if (problemScore > 0) return 'box bg-red';
    return 'box bg-black';
  }

  get studentProgressUrl(): string {
    return `/course/${this.course.alias}/student/${this.student.username}/`;
  }
}
</script>
