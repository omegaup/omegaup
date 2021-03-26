<template>
  <div class="panel">
    <h1 class="title">
      {{ title }}
      <template v-if="showVisibilityIndicators">
        <img
          v-if="problem.quality_seal || problem.visibility === 3"
          src="/media/quality-badge-sm.png"
          :title="T.wordsHighQualityProblem"
        />
        <span
          v-if="problem.visibility === 1 || problem.visibility === -1"
          class="glyphicon glyphicon-warning-sign"
          :title="T.wordsWarningProblem"
        ></span>
        <span
          v-if="problem.visibility === 0 || problem.visibility === -1"
          class="glyphicon glyphicon-eye-close"
          :title="T.wordsPrivate"
        ></span>
        <span
          v-if="problem.visibility <= -2"
          class="glyphicon glyphicon-ban-circle"
          :title="T.wordsBannedProblem"
        ></span>
      </template>
      <template v-if="showEditLink">
        (<a :href="`/problem/${problem.alias}/edit/`">{{ T.wordsEdit }}</a
        >)
      </template>
    </h1>
    <table v-if="problem.accepts_submissions">
      <tr>
        <th scope="row">{{ T.wordsPoints }}</th>
        <td>{{ problem.points }}</td>
        <th scope="row">{{ T.arenaCommonMemoryLimit }}</th>
        <td data-memory-limit>{{ memoryLimit }}</td>
      </tr>
      <tr>
        <th scope="row">{{ T.arenaCommonTimeLimit }}</th>
        <td>{{ timeLimit }}</td>
        <th scope="row">{{ T.arenaCommonOverallWallTimeLimit }}</th>
        <td>{{ overallWallTimeLimit }}</td>
      </tr>
      <tr>
        <template v-if="!showVisibilityIndicators">
          <th scope="row">{{ T.wordsInOut }}</th>
          <td>{{ T.wordsConsole }}</td>
        </template>
        <th scope="row">{{ T.problemEditFormInputLimit }}</th>
        <td>{{ inputLimit }}</td>
      </tr>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';

@Component
export default class ProblemSettingsSummary extends Vue {
  @Prop() problem!: types.ArenaProblemDetails;
  @Prop({ default: false }) showVisibilityIndicators!: boolean;
  @Prop({ default: false }) showEditLink!: boolean;

  T = T;

  get title(): string {
    if (this.showVisibilityIndicators) {
      return `${this.problem.problem_id}. ${this.problem.title}`;
    }
    if (!this.problem.letter) {
      return this.problem.title;
    }
    return `${this.problem.letter}. ${this.problem.title}`;
  }

  get memoryLimit(): string {
    if (!this.problem.settings?.limits.MemoryLimit) {
      return '';
    }
    if (typeof this.problem.settings?.limits.MemoryLimit === 'string') {
      return this.problem.settings?.limits.MemoryLimit;
    }
    const memoryLimit = this.problem.settings?.limits.MemoryLimit as number;
    return `${memoryLimit / 1024 / 1024} MiB`;
  }

  get timeLimit(): string {
    return `${this.problem.settings?.limits.TimeLimit}`;
  }

  get overallWallTimeLimit(): string {
    return `${this.problem.settings?.limits.OverallWallTimeLimit}`;
  }

  get inputLimit(): string {
    if (!this.problem.input_limit) {
      return '';
    }
    return `${this.problem.input_limit / 1024} KiB`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.panel {
  .title {
    text-align: center;
    font-size: 1.5em;
    margin: 1em;
  }
  table {
    width: 30em;
    margin: 10px auto;
    td {
      text-align: center;
    }
    th[scope='row'] {
      font-weight: bold;
    }
    td,
    th[scope='row'] {
      border: 1px solid $black;
      padding: 2px;
    }
  }
}
</style>
