<template>
  <div class="problem-list">
    <div class="summary" v-bind:class="{ active: !activeProblem }">
      <a class="name" href="#problems">{{ T.wordsSummary }}</a>
    </div>
    <div
      v-bind:class="{ active: problem.alias === activeProblem }"
      v-for="problem in problems"
    >
      <a
        class="name"
        v-on:click="$emit('navigate-to-problem', problem.alias)"
        >{{ problem.text }}</a
      >
      <span class="solved"
        >({{ parseFloat(problem.bestScore).toFixed(digitsAfterDecimalPoint) }}
        /
        {{
          parseFloat(problem.maxScore).toFixed(digitsAfterDecimalPoint)
        }})</span
      >
    </div>
  </div>
</template>

<style>
.problem-list > div {
  width: 19em;
  margin-bottom: 0.5em;
  background: #ddd;
  border: solid 1px #ccc;
  border-width: 1px 0 1px 1px;
  position: relative;
}

.problem-list > div a {
  color: #5588dd;
  display: block;
  padding: 0.5em;
  width: 100%;
  max-width: 212px;
}

.problem-list > div.active {
  background: white;
}

.problem-list > div.summary {
  margin-bottom: 1em;
}

.problem-list > div .solved {
  position: absolute;
  top: 0.5em;
  right: 1em;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup, T } from '../../omegaup';

@Component
export default class ArenaNavbarProblems extends Vue {
  @Prop() problems!: omegaup.ContestProblem[];
  @Prop() activeProblem!: string;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;

  T = T;
}
</script>
