<template>
  <div class="panel">
    <h1 class="title">
      {{ title }}
      <template v-if="!inArena">
        <img
          src="/media/quality-badge-sm.png"
          v-bind:title="T.wordsHighQualityProblem"
          v-if="problem.quality_seal || problem.visibility === 3"
        />
        <span
          class="glyphicon glyphicon-warning-sign"
          v-bind:title="T.wordsWarningProblem"
          v-if="problem.visibility === 1 || problem.visibility === -1"
        ></span>
        <span
          class="glyphicon glyphicon-eye-close"
          v-bind:title="T.wordsPrivate"
          v-if="problem.visibility === 0 || problem.visibility === -1"
        ></span>
        <span
          class="glyphicon glyphicon-ban-circle"
          v-bind:title="T.wordsBannedProblem"
          v-if="problem.visibility <= -2"
        ></span>
        <template v-if="isAdmin">
          (<a href="/problem/{$problem_alias}/edit/">{{ T.wordsEdit }}</a
          >)
        </template>
      </template>
    </h1>
    <table class="data">
      <tr>
        <td>{{ T.wordsPoints }}</td>
        <td>{{ problem.points }}</td>
        <td>{{ T.arenaCommonMemoryLimit }}</td>
        <td data-memory-limit>{{ memoryLimit }}</td>
      </tr>
      <tr>
        <td>{{ T.arenaCommonTimeLimit }}</td>
        <td>{{ timeLimit }}</td>
        <td>{{ T.arenaCommonOverallWallTimeLimit }}</td>
        <td>{{ overallWallTimeLimit }}</td>
      </tr>
      <tr>
        <template v-if="inArena">
          <td>{{ T.wordsInOut }}</td>
          <td>{{ T.wordsConsole }}</td>
        </template>
        <td>{{ T.problemEditFormInputLimit }}</td>
        <td>{{ inputLimit }}</td>
      </tr>
    </table>
  </div>
</template>

<style lang="scss">
#problem {
  .title {
    text-align: center;
    font-size: 1.5em;
    margin: 1em;
  }
  .data {
    width: 30em;
    margin: 10px auto;
    td {
      border: 1px solid #000;
      padding: 2px;
    }
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';
import { omegaup } from '../../omegaup';

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class ProblemSettingsSummary extends Vue {
  @Prop() problem!: omegaup.ArenaProblem;
  @Prop({ default: false }) inArena!: boolean;
  @Prop({ default: false }) isAdmin!: boolean;

  T = T;

  get title(): string {
    if (!this.inArena) {
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
