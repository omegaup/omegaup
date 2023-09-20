<template>
  <div class="card">
    <omegaup-markdown
      v-if="showSolution"
      :markdown="solution.markdown"
      :source-mapping="solution.sources"
      :image-mapping="solution.images"
    ></omegaup-markdown>
    <div v-else class="interstitial">
      <omegaup-markdown :markdown="statusMessage"></omegaup-markdown>
      <omegaup-markdown
        v-show="status === 'unlocked'"
        :markdown="ui.formatString(T.solutionUnlocked)"
      >
      </omegaup-markdown>

      <omegaup-markdown
        v-show="allowedSolutionsToSee !== null"
        :markdown="
          ui.formatString(T.solutionViewsLeft, {
            available: allowedSolutionsToSee,
            total: 5,
          }),
        "
      ></omegaup-markdown>

      <div class="text-center mt-5">
        <button
          v-if="status === 'unlocked'"
          class="btn btn-primary btn-md"
          @click="$emit('get-solution')"
        >
          {{ T.wordsSeeSolution }}
        </button>
        <button
          v-else-if="status === 'locked' && allowedSolutionsToSee === null"
          class="btn btn-secondary btn-md"
          @click="$emit('get-allowed-solutions')"
        >
          {{ T.solutionViewCurrentTokens }}
        </button>
        <button
          v-else-if="status === 'locked' && allowedSolutionsToSee &gt; 0"
          class="btn btn-primary btn-md"
          @click="$emit('unlock-solution')"
        >
          {{ T.wordsUnlockSolution }}
        </button>
        <!-- id-lint off -->
        <b-button
          id="popover-problem-solution"
          class="ml-1"
          size="sm"
          variant="none"
          @click="show = !show"
        >
          <font-awesome-icon :icon="['fas', 'question-circle']" />
        </b-button>
        <!-- id-lint on -->
        <b-popover
          target="popover-problem-solution"
          variant="info"
          placement="right"
        >
          <template #title>¿Cómo funciona?</template>
          {{
            '¡Si te has quedado sin puntos, vuelve mañana! Puedes ver hasta 5 soluciones por día.'
          }}
        </b-popover>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Import Bootstrap and BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import { ButtonPlugin, PopoverPlugin } from 'bootstrap-vue';
Vue.use(ButtonPlugin);
Vue.use(PopoverPlugin);

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    FontAwesomeIcon,
  },
})
export default class ProblemSolution extends Vue {
  @Prop() status!: string;
  @Prop({ default: null }) solution!: types.ProblemStatement | null;
  @Prop() allowedSolutionsToSee!: number;

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
