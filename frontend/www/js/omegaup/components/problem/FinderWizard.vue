<template>
  <transition name="modal">
    <div class="modal-mask" @click.self="$emit('close')">
      <omegaup-overlay-popup
        class="problem-finder-wizard-popup"
        @dismiss="$emit('close')"
      >
        <form-wizard
          color="#678DD7"
          :back-button-text="T.wordsBack"
          :finish-button-text="T.wordsConfirm"
          :next-button-text="T.wordsNext"
          :subtitle="T.wizardDescription"
          :title="T.wizardTitle"
          @on-complete="searchProblems"
          ><tab-content :title="T.wizardStepOne"
            ><toggle-button
              v-model="karel"
              :color="{ checked: '#678DD7', unchecked: '#343a40' }"
              :font-size="12"
              :height="35"
              :labels="{
                checked: `${T.wordsKarel}`,
                unchecked: `${T.wordsAnyLanguage}`,
              }"
              :value="karel"
              :width="160"
            ></toggle-button>
            <multiselect
              v-model="selectedTags"
              :options="filteredTags"
              :multiple="true"
              :internal-search="false"
              :close-on-select="false"
              :clear-on-select="false"
              :preserve-search="true"
              :placeholder="T.wordsAddTag"
              :show-labels="false"
              :show-no-results="false"
              :show-no-options="false"
              label="value"
              track-by="key"
              @search-change="onTagSearch"
            ></multiselect
          ></tab-content>
          <tab-content :title="T.wizardStepTwo"
            ><vue-slider
              v-model="difficultyRange"
              tooltip="none"
              :adsorb="true"
              :dot-size="18"
              :enable-cross="false"
              :included="true"
              :marks="SLIDER_MARKS"
              :max="4"
              :min="0"
            ></vue-slider
          ></tab-content>
          <tab-content :title="T.wizardStepThree">
            <div class="tab-select">
              <label
                v-for="priority in PRIORITIES"
                class="tab-select-el"
                :class="{
                  'tab-select-el-active': priority.type === selectedPriority,
                }"
                >{{ priority.text }}
                <input
                  v-model="selectedPriority"
                  class="hidden-radio"
                  name="priority"
                  type="radio"
                  :value="priority.type"
              /></label>
            </div> </tab-content
        ></form-wizard>
      </omegaup-overlay-popup>
    </div>
  </transition>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
// https://binarcode.github.io/vue-form-wizard/
import { FormWizard, TabContent } from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
// https://www.npmjs.com/package/vue-js-toggle-button
import { ToggleButton } from 'vue-js-toggle-button';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
// https://nightcatsama.github.io/vue-slider-component/
import VueSlider from 'vue-slider-component';
import 'vue-slider-component/theme/default.css';
import omegaup_OverlayPopup from '../OverlayPopup.vue';

interface Priority {
  type: string;
  text: string;
}

interface TagObject {
  key: string;
  value: string;
}

function normalizeForSearch(value: string): string {
  return value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();
}

@Component({
  components: {
    FormWizard,
    TabContent,
    ToggleButton,
    Multiselect,
    VueSlider,
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class ProblemFinderWizard extends Vue {
  @Prop() possibleTags!: { name: string }[];
  @Prop({ default: false }) show!: boolean;

  T = T;
  karel = false;
  selectedTags: TagObject[] = [];
  tagQuery = '';
  difficultyRange = [0, 4];
  SLIDER_MARKS: { [key: string]: string } = {
    '0': T.qualityFormDifficultyVeryEasy,
    '1': T.qualityFormDifficultyEasy,
    '2': T.qualityFormDifficultyMedium,
    '3': T.qualityFormDifficultyHard,
    '4': T.qualityFormDifficultyVeryHard,
  };
  selectedPriority = 'quality';
  PRIORITIES: Priority[] = [
    {
      type: 'quality',
      text: T.wordsQuality,
    },
    {
      type: 'points',
      text: T.wordsPointsForRank,
    },
    {
      type: 'submissions',
      text: T.wizardPriorityPopularity,
    },
  ];

  get filteredTags(): TagObject[] {
    const query = normalizeForSearch(this.tagQuery);
    if (!query) return this.tagObjects;
    return this.tagObjects.filter((tag) =>
      normalizeForSearch(tag.value).includes(query),
    );
  }

  onTagSearch(query: string): void {
    this.tagQuery = query;
  }

  get tagObjects(): TagObject[] {
    const tagObjects: TagObject[] = [];
    this.possibleTags.forEach((tagObject) => {
      if (!Object.prototype.hasOwnProperty.call(T, tagObject.name)) {
        return;
      }
      tagObjects.push({
        key: tagObject.name,
        value: T[tagObject.name],
      });
    });
    return tagObjects;
  }

  searchProblems(): void {
    // Build query parameters
    let queryParameters: omegaup.QueryParameters = {
      some_tags: true,
      difficulty_range: `${this.difficultyRange[0].toString()},${this.difficultyRange[1].toString()}`,
      order_by: this.selectedPriority,
      sort_order: 'desc',
    };
    if (this.karel) {
      queryParameters.only_karel = true;
    }
    if (this.selectedTags.length > 0) {
      queryParameters.tag = this.selectedTags.map((tag) => tag.key);
    }
    this.$emit('search-problems', queryParameters);
  }

  mounted(): void {
    document.addEventListener('keydown', this.onEsc);
  }

  beforeDestroy(): void {
    document.removeEventListener('keydown', this.onEsc);
  }

  onEsc(event: KeyboardEvent): void {
    if (event.key !== 'Escape' || !this.show) {
      return;
    }
    // Let form elements handle ESC themselves (e.g. dismissing the typeahead)
    // instead of closing the whole wizard.
    const target = event.target as HTMLElement | null;
    if (
      target &&
      (target.tagName === 'INPUT' ||
        target.tagName === 'TEXTAREA' ||
        target.tagName === 'SELECT' ||
        target.isContentEditable)
    ) {
      return;
    }
    this.$emit('close');
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.modal-mask {
  position: fixed;
  z-index: 99999;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(var(--finder-wizard-modal-mask-background-color), 0.5);
  transition: opacity 0.3s ease;
}

.problem-finder-wizard-popup {
  max-width: 800px;
}

.wizard-tab-content {
  text-align: center;
}

/* stylelint-disable-next-line selector-pseudo-element-no-unknown */
::v-deep .multiselect {
  margin: 1em 0 3em;
}

/* stylelint-disable-next-line selector-pseudo-element-no-unknown */
::v-deep .multiselect__input {
  padding-left: 0.25em;
}

/* stylelint-disable-next-line selector-pseudo-element-no-unknown */
::v-deep .multiselect__tag {
  font-size: 1em;
  background-color: var(--finder-wizard-slider-process-background-color);
}

/* stylelint-disable-next-line selector-pseudo-element-no-unknown */
::v-deep .multiselect__option--highlight,
.vue-slider-process {
  background-color: var(--finder-wizard-slider-process-background-color);
}

.vue-slider {
  margin: 1em 2em 5em;
}

.vue-slider-rail {
  height: 12.5px;
}

.tab-select {
  margin: 2em 3em 3em;
  width: 90%;
  display: flex;
}

.tab-select-el {
  display: block;
  cursor: pointer;
  padding: 0.25em 1em;
  border: 1px solid var(--finder-wizard-tab-select-el-border-color);
  flex: 1;
  text-align: center;
  color: var(--finder-wizard-tab-select-el-font-color);
}

.tab-select-el:hover,
.tab-select-el-active {
  color: var(--finder-wizard-tab-select-el-font-color--active);
  background: var(--finder-wizard-tab-select-el-background-color--active);
}

.hidden-radio {
  display: none;
}
</style>
