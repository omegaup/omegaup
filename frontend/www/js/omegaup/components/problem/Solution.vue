<template>
  <div class="panel">
    <omegaup-markdown
      v-if="showSolution"
      v-bind:markdown="solution.markdown"
      v-bind:image-mapping="solution.images"
    ></omegaup-markdown>
    <div class="interstitial" v-else="showSolution">
      <p v-html="statusMessage"></p>
      <p
        v-html="
          ui.formatString(T.solutionTokens, {
            available: availableTokens,
            total: allTokens,
          })
        "
        v-show="allTokens !== null && availableTokens !== null"
      ></p>
      <div class="text-center">
        <button
          class="btn btn-primary btn-md"
          v-if="status === 'unlocked'"
          v-on:click="$emit('get-solution')"
        >
          {{ T.wordsSeeSolution }}
        </button>
        <button
          class="btn btn-primary btn-md"
          v-else-if="
            status === 'locked' &&
            allTokens === null &&
            availableTokens === null
          "
          v-on:click="$emit('get-tokens')"
        >
          {{ T.solutionViewCurrentTokens }}
        </button>
        <button
          class="btn btn-primary btn-md"
          v-else-if="status === 'locked' && availableTokens &gt; 0"
          v-on:click="$emit('unlock-solution')"
        >
          {{ T.wordsUnlockSolution }}
        </button>
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
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class ProblemSolution extends Vue {
  @Prop() status!: string;
  @Prop({ default: null }) solution!: types.ProblemStatement | null;
  @Prop() availableTokens!: number;
  @Prop() allTokens!: number;

  T = T;
  ui = ui;

  get showSolution(): boolean {
    return this.status === 'unlocked' && this.solution !== null;
  }

  get statusMessage(): string {
    switch (this.status) {
      case 'unlocked':
        return T.solutionConfirm;
      case 'locked':
        return T.solutionLocked;
      case 'not_found':
        return T.solutionNotFound;
      case 'not_logged_in':
        return T.solutionNotLoggedIn;
      default:
        return '';
    }
  }
}
</script>
