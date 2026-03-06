<template>
  <div class="mt-4 presentation-wrapper" :style="{ fontSize: fontSize + 'px' }">
    <div
      class="presentation-controls d-flex align-items-center flex-wrap mb-3 p-2 bg-light border rounded"
    >
      <a href="" class="mr-3" @click.prevent="$emit('print-page')">
        <font-awesome-icon
          :title="T.contestAndProblemPrintButtonDesc"
          :icon="['fas', 'print']"
      /></a>

      <div class="d-flex align-items-center mr-3">
        <span class="mr-2 text-muted small">{{ T.fontSize }}:</span>
        <button
          class="btn btn-sm btn-outline-secondary mr-1"
          :title="T.problemPresentationModeFontDec"
          :disabled="fontSize <= 12"
          @click="fontSize = Math.max(12, fontSize - 2)"
        >
          <font-awesome-icon :icon="['fas', 'search-minus']" />
        </button>
        <span class="px-2">{{ fontSize }}px</span>
        <button
          class="btn btn-sm btn-outline-secondary ml-1"
          :title="T.problemPresentationModeFontInc"
          :disabled="fontSize >= 36"
          @click="fontSize = Math.min(36, fontSize + 2)"
        >
          <font-awesome-icon :icon="['fas', 'search-plus']" />
        </button>
      </div>

      <button
        class="btn btn-sm btn-outline-secondary"
        :title="T.problemPresentationModeToggleLimits"
        @click="showLimits = !showLimits"
      >
        <font-awesome-icon :icon="['fas', 'table']" />
        {{ T.problemPresentationModeToggleLimits }}
      </button>
    </div>

    <omegaup-problem-settings-summary
      v-show="showLimits"
      :problem="problem"
      :show-classroom-view-link="false"
    ></omegaup-problem-settings-summary>
    <omegaup-markdown
      :markdown="problem.statement.markdown"
      :source-mapping="problem.statement.sources"
      :image-mapping="problem.statement.images"
      :problem-settings="problem.settings"
    ></omegaup-markdown>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import problem_SettingsSummary from './SettingsSummary.vue';
import omegaup_problemMarkdown from './ProblemMarkdown.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faPrint,
  faSearchMinus,
  faSearchPlus,
  faTable,
} from '@fortawesome/free-solid-svg-icons';
library.add(faPrint, faSearchMinus, faSearchPlus, faTable);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_problemMarkdown,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
  },
})
export default class ProblemPrint extends Vue {
  @Prop() problem!: types.ProblemInfo;

  T = T;
  fontSize = 18;
  showLimits = true;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

table td {
  padding: 0.5rem;
}

.presentation-controls {
  gap: 0.5rem;
}
</style>

