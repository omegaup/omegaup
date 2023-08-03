<template>
  <transition name="modal">
    <div class="modal-mask">
      <div class="modal-container">
        <div class="d-flex justify-content-end">
          <button class="btn" @click="$emit('close')">❌</button>
        </div>
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
            <tags-input
              v-model="selectedTags"
              element-id="tags"
              :existing-tags="tagObjects"
              :only-existing-tags="true"
              :placeholder="T.wordsAddTag"
              :typeahead="true"
            ></tags-input
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
      </div>
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
// https://github.com/voerro/vue-tagsinput
import VoerroTagsInput from '@voerro/vue-tagsinput';
import '@voerro/vue-tagsinput/dist/style.css';
// https://nightcatsama.github.io/vue-slider-component/
import VueSlider from 'vue-slider-component';
import 'vue-slider-component/theme/default.css';

interface Priority {
  type: string;
  text: string;
}

interface TagObject {
  key: string;
  value: string;
}

@Component({
  components: {
    FormWizard,
    TabContent,
    ToggleButton,
    'tags-input': VoerroTagsInput,
    VueSlider,
  },
})
export default class ProblemFinderWizard extends Vue {
  @Prop() possibleTags!: { name: string }[];

  T = T;
  karel = false;
  selectedTags: TagObject[] = [];
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

.modal-container {
  background: var(--finder-wizard-modal-container-background-color);
  min-width: 340px;
  max-width: 800px;
  margin: 2.5em auto 0;
  border: 2px solid var(--finder-wizard-modal-container-border-color);
  padding: 1em;
  position: relative;
  overflow: auto;
}

.close {
  float: none;
  text-align: right;
  width: 100%;
}

.wizard-tab-content {
  text-align: center;
}

.tags-input {
  margin: 1em 0 3em;
}

.tags-input input {
  padding-left: 0.25em;
}

.tags-input-remove {
  width: 1rem;
  height: 1rem;
}

.tags-input-badge {
  font-size: 1em;
}

.typeahead-badges {
  margin-top: 0.35em;
}

.tags-input-remove::before,
.tags-input-remove::after,
.tags-input-typeahead-item-highlighted-default,
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
