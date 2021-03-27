<template>
  <omegaup-overlay-popup @dismiss="onCloseModal(currentView)">
    <transition name="fade">
      <form data-promotion-popup class="h-auto w-auto" @submit.prevent="">
        <div class="container-fluid d-flex align-items-start flex-column">
          <template v-if="currentView === AvailableViews.Content">
            <slot
              name="popup-content"
              :onSubmit="onSubmit"
              :sortedProblemTags="sortedProblemTags"
              :onHide="onHide"
            >
              <p class="h4 font-weight-bold pb-4 text-center w-100">
                {{ solved ? T.qualityFormCongrats : T.qualityFormRateBeforeAc }}
              </p>
              <div class="form-group w-100">
                <label class="w-100">{{ T.qualityFormDifficulty }}</label>
                <div class="container-fluid">
                  <div
                    v-for="difficultyLevel in difficultyLevels"
                    :key="difficultyLevel.id"
                    class="form-check form-check-inline"
                  >
                    <label class="form-check-label">
                      <input
                        v-model="difficulty"
                        type="radio"
                        :value="difficultyLevel.id"
                      />
                      {{ difficultyLevel.description }}
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group w-100">
                <label class="w-100">
                  {{ T.qualityFormTags }}
                  <ul class="tag-select">
                    <li
                      v-for="problemTopic in sortedProblemTags"
                      :key="problemTopic.value"
                      class="tag-select"
                    >
                      <label class="tag-label"
                        ><input
                          v-model="tags"
                          type="checkbox"
                          :value="problemTopic.value"
                        />
                        {{ problemTopic.text }}</label
                      >
                    </li>
                  </ul>
                </label>
              </div>
              <div class="w-100 mb-3">
                <label class="mb-2 w-100">{{ T.qualityFormQuality }}</label>
                <div class="container-fluid">
                  <div
                    v-for="qualityLevel in qualityLevels"
                    :key="qualityLevel.id"
                    class="form-check form-check-inline"
                  >
                    <label class="form-check-label">
                      <input
                        v-model="quality"
                        type="radio"
                        :value="qualityLevel.id"
                      />
                      {{ qualityLevel.description }}
                    </label>
                  </div>
                </div>
              </div>
              <div class="text-right w-100">
                <button
                  class="col-md-4 mr-2 mb-1 btn btn-primary"
                  type="submit"
                  :disabled="!quality && !tags.length && !difficulty"
                  @click="onSubmit"
                >
                  {{ T.wordsSend }}
                </button>
                <button
                  class="col-md-4 mb-1 btn btn-secondary"
                  type="button"
                  @click="onHide(true)"
                >
                  {{ T.wordsCancel }}
                </button>
              </div>
            </slot>
          </template>
          <template v-if="currentView === AvailableViews.Thanks">
            <div class="w-100 h-100 h3 text-center">
              {{ T.qualityFormThanksForReview }}
            </div>
          </template>
        </div>
      </form>
    </transition>
  </omegaup-overlay-popup>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup_OverlayPopup from '../OverlayPopup.vue';
import { AvailableViews } from './DemotionPopup.vue';
import T from '../../lang';

interface ProblemTag {
  text: string;
  value: string;
}

interface DifficultyLevel {
  id: number;
  description: string;
}

interface QualityLevel {
  id: number;
  description: string;
}

@Component({
  components: {
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class QualityPromotionPopup extends Vue {
  @Prop({ default: false }) solved!: boolean;
  @Prop({ default: false }) tried!: boolean;
  @Prop({
    default: () => [
      'problemTopic2Sat',
      'problemTopicArrays',
      'problemTopicBacktracking',
      'problemTopicBigNumbers',
      'problemTopicBinarySearch',
      'problemTopicBitmasks',
      'problemTopicBreadthDepthFirstSearch',
      'problemTopicBruteForce',
      'problemTopicBuckets',
      'problemTopicCombinatorics',
      'problemTopicDataStructures',
      'problemTopicDisjointSets',
      'problemTopicDivideAndConquer',
      'problemTopicDynamicProgramming',
      'problemTopicFastFourierTransform',
      'problemTopicGameTheory',
      'problemTopicGeometry',
      'problemTopicGraphTheory',
      'problemTopicGreedy',
      'problemTopicHashing',
      'problemTopicIfElseSwitch',
      'problemTopicImplementation',
      'problemTopicInputOutput',
      'problemTopicLoops',
      'problemTopicMath',
      'problemTopicMatrices',
      'problemTopicMaxFlow',
      'problemTopicMeetInTheMiddle',
      'problemTopicNumberTheory',
      'problemTopicParsing',
      'problemTopicProbability',
      'problemTopicShortestPath',
      'problemTopicSimulation',
      'problemTopicSorting',
      'problemTopicStackQueue',
      'problemTopicStrings',
      'problemTopicSuffixArray',
      'problemTopicSuffixTree',
      'problemTopicTernarySearch',
      'problemTopicTrees',
      'problemTopicTwoPointers',
    ],
  })
  possibleTags!: string[];
  @Prop({
    default: () => [
      { id: 0, description: T.qualityFormDifficultyVeryEasy },
      { id: 1, description: T.qualityFormDifficultyEasy },
      { id: 2, description: T.qualityFormDifficultyMedium },
      { id: 3, description: T.qualityFormDifficultyHard },
      { id: 4, description: T.qualityFormDifficultyVeryHard },
    ],
  })
  difficultyLevels!: DifficultyLevel[];
  @Prop({
    default: () => [
      { id: 0, description: T.qualityFormQualityVeryBad },
      { id: 1, description: T.qualityFormQualityBad },
      { id: 2, description: T.qualityFormQualityFair },
      { id: 3, description: T.qualityFormQualityGood },
      { id: 4, description: T.qualityFormQualityVeryGood },
    ],
  })
  qualityLevels!: QualityLevel[];

  AvailableViews = AvailableViews;
  T = T;
  currentView: AvailableViews = AvailableViews.Content;
  difficulty = '';
  quality = '';
  tags: string[] = [];

  get sortedProblemTags(): ProblemTag[] {
    return this.possibleTags
      .map(
        (x: string): ProblemTag => {
          return {
            value: x,
            text: T[x],
          };
        },
      )
      .sort((a: ProblemTag, b: ProblemTag): number => {
        return a.text.localeCompare(b.text, T.lang);
      });
  }

  get sortedProblemTextTags(): string[] {
    return this.sortedProblemTags.map((x: ProblemTag): string => x.text);
  }

  onCloseModal(currentView: AvailableViews): void {
    if (currentView !== AvailableViews.Thanks) {
      this.onDismiss();
      return;
    }
    this.onHide(false);
  }

  onHide(isDismissed: boolean): void {
    this.$emit('dismiss', this, isDismissed);
  }

  onDismiss(): void {
    this.$emit('dismiss', this, /*isDismissed=*/ true);
  }

  onSubmit(): void {
    this.$emit('submit', this);
    this.currentView = AvailableViews.Thanks;

    setTimeout(() => this.onHide(false), 2000);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
ul.tag-select {
  height: 185px;
  overflow: auto;
  border: 1px solid var(--quality-nomination-tag-select-border-color);
  background: var(--quality-nomination-tag-select-background-color);
  list-style-type: none;
}
.tag-label {
  width: -webkit-fill-available;
  margin-bottom: 0;
  padding-bottom: 0.5rem;
}
</style>
