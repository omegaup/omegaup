<template>
  <div class="panel">
    <div class="interstitial"
         v-if="showSolutionPanel">
      <p>{{ T.solutionConfirm }}</p>
      <div class="text-center">
        <a class="btn btn-primary btn-md"
             v-on:click="showPanel">{{ T.wordsSeeSolution }}</a>
      </div>
    </div>
    <div v-else="">
      <div class="solution"
           v-html="solution"
           v-if="status === 'unlocked'"></div>
      <div class="interstitial"
           v-else="">
        <p>{{ statusMessage }}</p>
        <div v-if="status === 'locked'">
          <p>{{ T.solutionTokenDescription }}</p>
          <p class="solution-tokens"
             v-html=
             "UI.formatString(T.solutionTokens, { available: availableTokens, total: allTokens, })">
             </p>
          <div class="text-center"
               v-if="availableTokens &gt; 0">
            <a class="btn btn-primary btn-md"
                 v-on:click="$emit('unlock-solution')">{{ T.wordsUnlockSolution }}</a>
          </div>
        </div>
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
  showSolutionPanel = true;

  get statusMessage(): string {
    switch (this.status) {
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

  showPanel(): void {
    this.showSolutionPanel = false;
    this.$emit('get-initial-content');
  }
}

</script>
