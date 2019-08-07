<template>
  <div class="panel">
    <div class="solution"
         v-html="solution"
         v-if="status === 'unlocked' &amp;&amp; solution !== null"></div>
    <div class="interstitial"
         v-else="">
      <p>{{ statusMessage }}</p>
      <div class="text-center">
        <a class="btn btn-primary btn-md"
             v-if="status === 'locked'"
             v-on:click="$emit('unlock-solution')">{{ T.wordsUnlockSolution }}</a> <a class=
             "btn btn-primary btn-md"
             v-if="status === 'unlocked'"
             v-on:click="$emit('get-solution');">{{ T.wordsSeeSolution }}</a>
      </div>
    </div>
    <div class="interstitial"
         v-else="">
      <div v-if="status === 'locked'">
        <p>{{ T.solutionTokenDescription }}</p>
        <div class="text-center">
          <a class="btn btn-primary btn-md"
               v-on:click="$emit('unlock-solution')">{{ T.wordsUnlockSolution }}</a>
        </div>
      </div>
    </div><!--
    <div class="interstitial"
         v-else-if="status === 'unlocked' &amp;&amp; solution === null">
      <p>{{ T.solutionConfirm }}</p>
      <div class="text-center">
        <a class="btn btn-primary btn-md"
             v-on:click="$emit('get-solution');">{{ T.wordsSeeSolution }}</a>
      </div>
    </div>
    <div class="interstitial"
         v-else="">
      <p>{{ statusMessage }}</p>
      <div v-if="status === 'locked'">
        <p>{{ T.solutionTokenDescription }}</p>
        <div class="text-center">
          <a class="btn btn-primary btn-md"
               v-on:click="$emit('unlock-solution')">{{ T.wordsUnlockSolution }}</a>
        </div>
      </div>
    </div>
    -->
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
