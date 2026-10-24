<template>
  <omegaup-overlay-popup @dismiss="onCloseModal(currentView)">
    <transition name="fade">
      <form data-promotion-popup class="modal-form" @submit.prevent="">
        <div class="container-fluid d-flex align-items-start flex-column">
          <template v-if="currentView === AvailableViews.Content">
            <slot name="popup-content" :onSubmit="onSubmit" :onHide="onHide">
              <p class="modal-form__title text-center">
                {{ solved ? T.qualityFormCongrats : T.qualityFormRateBeforeAc }}
              </p>
              <div class="modal-form__field">
                <label class="modal-form__label">{{
                  T.qualityFormDifficulty
                }}</label>
                <div class="container-fluid">
                  <div
                    v-for="difficultyLevel in difficultyLevels"
                    :key="difficultyLevel.id"
                    class="form-check form-check-inline"
                  >
                    <label class="form-check-label">
                      <input
                        v-model="difficulty"
                        data-dificulty-radio-button
                        type="radio"
                        :value="difficultyLevel.id"
                      />
                      {{ difficultyLevel.description }}
                    </label>
                  </div>
                </div>
              </div>
              <div class="modal-form__field">
                <label class="modal-form__label">{{
                  T.qualityFormQuality
                }}</label>
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
              <div class="modal-form__actions">
                <button
                  data-submit-feedback-button
                  class="btn btn-primary"
                  type="submit"
                  :disabled="!quality && !difficulty"
                  @click="onSubmit"
                >
                  {{ T.wordsSend }}
                </button>
                <button
                  class="btn btn-secondary"
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
import { defineComponent, PropType } from 'vue';
import omegaup_OverlayPopup from '../OverlayPopup.vue';
import { AvailableViews } from './DemotionPopup.vue';
import T from '../../lang';

interface DifficultyLevel {
  id: number;
  description: string;
}

interface QualityLevel {
  id: number;
  description: string;
}

export default defineComponent({
  name: 'QualityPromotionPopup',
  components: {
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
  props: {
    solved: {
      type: Boolean,
      default: false,
    },
    tried: {
      type: Boolean,
      default: false,
    },
    difficultyLevels: {
      type: Array as PropType<DifficultyLevel[]>,
      default: () => [
        { id: '0', description: T.qualityFormDifficultyVeryEasy },
        { id: '1', description: T.qualityFormDifficultyEasy },
        { id: '2', description: T.qualityFormDifficultyMedium },
        { id: '3', description: T.qualityFormDifficultyHard },
        { id: '4', description: T.qualityFormDifficultyVeryHard },
      ],
    },
    qualityLevels: {
      type: Array as PropType<QualityLevel[]>,
      default: () => [
        { id: '0', description: T.qualityFormQualityVeryBad },
        { id: '1', description: T.qualityFormQualityBad },
        { id: '2', description: T.qualityFormQualityFair },
        { id: '3', description: T.qualityFormQualityGood },
        { id: '4', description: T.qualityFormQualityVeryGood },
      ],
    },
  },
  emits: ['dismiss', 'submit'],
  data() {
    return {
      AvailableViews,
      T,
      currentView: AvailableViews.Content as AvailableViews,
      difficulty: '',
      quality: '',
    };
  },
  methods: {
    onCloseModal(currentView: AvailableViews): void {
      if (currentView !== AvailableViews.Thanks) {
        this.onDismiss();
        return;
      }
      this.onHide(false);
    },

    onHide(isDismissed: boolean): void {
      this.$emit('dismiss', this, isDismissed);
    },

    onDismiss(): void {
      this.$emit('dismiss', this, /*isDismissed=*/ true);
    },

    onSubmit(): void {
      this.$emit('submit', {
        solved: this.solved,
        tried: this.tried,
        difficulty: this.difficulty,
        quality: this.quality,
      });
      this.currentView = AvailableViews.Thanks;

      setTimeout(() => this.onHide(false), 2000);
    },
  },
});
</script>
