<template>
  <div class="problem-list">
    <div class="summary" v-bind:class="{ active: !activeProblem }">
      <a class="name" href="#problems">{{ T.wordsSummary }}</a>
    </div>
    <div
      v-bind:class="{ active: problem.alias === activeProblem }"
      v-for="problem in problems"
    >
      <div class="row">
        <div class="col-xs-6 problem-type">
          <span v-if="inAssignment">{{
            getProblemTypeTitle(problem.acceptsSubmissions)
          }}</span>
        </div>
        <div class="col-xs-6 solved" v-if="problem.acceptsSubmissions">
          <span
            >({{
              parseFloat(problem.bestScore).toFixed(digitsAfterDecimalPoint)
            }}
            /
            {{
              parseFloat(problem.maxScore).toFixed(digitsAfterDecimalPoint)
            }})</span
          >
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <a
            class="name"
            v-on:click="$emit('navigate-to-problem', problem.alias)"
            >{{ problem.text }}</a
          >
        </div>
      </div>
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
}

.problem-list > div.active {
  background: white;
}

.problem-list > div.summary {
  margin-bottom: 1em;
}

.problem-list > div .solved {
  text-align: right;
  right: 1em;
}

.problem-list .problem-type {
  font-size: 13px;
  color: #9a9a9a;
  font-weight: bold;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

@Component
export default class ArenaNavbarProblems extends Vue {
  @Prop() problems!: omegaup.ContestProblem[];
  @Prop() activeProblem!: string;
  @Prop() inAssignment!: boolean;
  @Prop({ default: 2 }) digitsAfterDecimalPoint!: number;

  T = T;

  getProblemTypeTitle(acceptsSubmissions: boolean): string {
    return acceptsSubmissions ? T.wordsProblem : T.wordsLecture;
  }
}
</script>
