<template>
  <div class="presentation-mode-root container-fluid px-4">
    <div class="presentation-controls d-flex align-items-center flex-wrap mb-3 p-2 bg-light border rounded">
      <div class="d-flex align-items-center mr-3">
        <span class="mr-2 text-muted small">{{ T.fontSize }}:</span>
        <button
          class="btn btn-sm btn-outline-secondary mr-1"
          :title="T.problemPresentationModeFontDec"
          :disabled="isFontAtMin"
          @click="decreaseFontSize"
        >
          <font-awesome-icon :icon="['fas', 'search-minus']" />
        </button>
        <span class="px-2 font-size-indicator">{{ fontSize }}px</span>
        <button
          class="btn btn-sm btn-outline-secondary ml-1"
          :title="T.problemPresentationModeFontInc"
          :disabled="isFontAtMax"
          @click="increaseFontSize"
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

    <div class="limits-wrapper">
      <omegaup-problem-settings-summary
        v-show="showLimits"
        class="presentation-limits-table"
        :problem="problem"
        :show-classroom-view-link="false"
      ></omegaup-problem-settings-summary>
    </div>

    <div :class="fontSizeClass" class="mt-4 presentation-content">
      <omegaup-markdown
        :markdown="problem.statement.markdown"
        :source-mapping="problem.statement.sources"
        :image-mapping="problem.statement.images"
        :problem-settings="problem.settings"
      ></omegaup-markdown>
    </div>
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
  faSearchMinus,
  faSearchPlus,
  faTable,
} from '@fortawesome/free-solid-svg-icons';
library.add(faSearchMinus, faSearchPlus, faTable);

const MIN_FONT_SIZE = 48;
const MAX_FONT_SIZE = 76;
const FONT_STEP = 6;
const DEFAULT_FONT_SIZE = 48;

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_problemMarkdown,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
  },
})
export default class ProblemPresentationMode extends Vue {
  @Prop() problem!: types.ProblemInfo;

  T = T;
  fontSize = DEFAULT_FONT_SIZE;
  showLimits = false;

  get isFontAtMin(): boolean {
    return this.fontSize <= MIN_FONT_SIZE;
  }

  get isFontAtMax(): boolean {
    return this.fontSize >= MAX_FONT_SIZE;
  }

  get fontSizeClass(): string {
    return `presentation-font-${this.fontSize}`;
  }

  decreaseFontSize(): void {
    this.fontSize = Math.max(MIN_FONT_SIZE, this.fontSize - FONT_STEP);
  }

  increaseFontSize(): void {
    this.fontSize = Math.min(MAX_FONT_SIZE, this.fontSize + FONT_STEP);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.presentation-mode-root {
  /* Hide scroll-to-top button in presentation mode */
  :global(#scroll-to-top) {
    display: none !important;
  }
}

.presentation-controls {
  gap: 0.5rem;
  position: sticky;
  top: 0;
  z-index: 10;
}

.font-size-indicator {
  font-size: 14px !important;
  white-space: nowrap;
}

.limits-wrapper {
  :deep(.presentation-limits-table) {
    font-size: 36px;

    table {
      font-size: 36px;

      td,
      th {
        padding: 0.5rem;
      }
    }
  }
}

/* Font size classes for the content wrapper */
.presentation-font-48 { font-size: 48px; }
.presentation-font-54 { font-size: 54px; }
.presentation-font-60 { font-size: 60px; }
.presentation-font-66 { font-size: 66px; }
.presentation-font-72 { font-size: 72px; }
.presentation-font-76 { font-size: 76px; }
</style>
