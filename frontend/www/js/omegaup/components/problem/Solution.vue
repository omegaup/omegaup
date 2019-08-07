<template>
  <div class="panel">
    <div class="solution"
         v-html="solution"
         v-if="status === 'unlocked' &amp;&amp; solution !== null"></div>
    <div class="interstitial"
         v-else="">
      <p>{{ statusMessage }}</p>
      <p v-html=
      "UI.formatString(T.solutionTokens, { available: availableTokens, total: allTokens, })"
         v-show="allTokens !== null &amp;&amp; availableTokens !== null"></p>
      <div class="text-center">
        <a class="btn btn-primary btn-md"
             v-if="status === 'unlocked'"
             v-on:click="$emit('get-solution');">{{ T.wordsSeeSolution }}</a> <a class=
             "btn btn-primary btn-md"
             v-else-if=
             "status === 'locked' &amp;&amp; allTokens === null &amp;&amp; availableTokens === null"
             v-on:click="$emit('get-tokens')">{{ T.solutionViewCurrentTokens }}</a> <a class=
             "btn btn-primary btn-md"
             v-else-if="status === 'locked' &amp;&amp; availableTokens &gt; 0"
             v-on:click="$emit('unlock-solution')">{{ T.wordsUnlockSolution }}</a>
      </div>
    </div>
  </div>
</template>

<style>
.interstitial {
  padding: 2em;
}

.solution {
  padding: 2em 7em;
}

.solution-tokens {
  font-size: 1.25em;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class ProblemSolution extends Vue {
  @Prop() status!: string;
  @Prop() solution!: string;
  @Prop() availableTokens!: number;
  @Prop() allTokens!: number;

  T = T;
  UI = UI;

  get statusMessage(): string {
    switch (this.status) {
      case 'unlocked':
        return this.T.solutionConfirm;
      case 'locked':
        return this.T.solutionLocked;
      case 'not_found':
        return this.T.solutionNotFound;
      case 'not_logged_in':
        return this.T.solutionNotLoggedIn;
      default:
        return '';
    }
  }
}

</script>
