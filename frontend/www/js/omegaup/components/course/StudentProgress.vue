<template>
  <tr>
    <td class="text-center align-middle">
      <a :href="studentProgressUrl">
        {{ student.name || student.username }}
      </a>
    </td>
    <td
      v-for="assignment in assignments"
      :key="assignment.alias"
      class="score flex-column justify-content-center align-items-center"
    >
      <omegaup-markdown
        :markdown="getProgressDescription(assignment.alias)"
      ></omegaup-markdown>
      <div class="d-flex justify-content-center">
        <div
          v-if="
            !Object.prototype.hasOwnProperty.call(
              student.progress,
              assignment.alias,
            )
          "
        >
          {{ T.wordsProblemsUnsolved }}
        </div>
        <div
          v-else
          class="d-flex border border-dark"
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
          ></div>
        </div>
      </div>
    </td>
  </tr>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import * as ui from '../../ui';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
  directives: {
    tooltip: VTooltip,
  },
})
export default class StudentProgress extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() student!: types.StudentProgress;
  @Prop() assignments!: omegaup.Assignment[];

  T = T;

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

  getProgressDescription(assignmentAlias: string): string {
    const score = this.score(assignmentAlias);
    const points = this.points(assignmentAlias);
    if (points === 0) {
      return T.studentProgressOnlyLecturesDescription;
    }
    return ui.formatString(T.studentProgressDescription, {
      score: score,
      points: points,
      progress: (points != 0 ? (score / points) * 100 : 0).toFixed(2),
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
    return ui.formatString(T.studentProgressTooltipDescription, {
      problem: problemAlias,
      score: this.getScore(assignmentAlias, problemAlias),
      points: this.getPoints(assignmentAlias, problemAlias),
      progress: this.getProgress(assignmentAlias, problemAlias),
    });
  }

  get studentProgressUrl(): string {
    return `/course/${this.course.alias}/student/${this.student.username}/`;
  }
}
</script>

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
