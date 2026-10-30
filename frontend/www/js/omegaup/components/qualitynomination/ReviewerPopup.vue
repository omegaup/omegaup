<template>
  <omegaup-overlay-popup @dismiss="onHide">
    <transition name="fade">
      <form data-reviewewr-popup class="modal-form" @submit.prevent="">
        <div class="container-fluid d-flex align-items-start flex-column">
          <template v-if="currentView === AvailableViews.Content">
            <p class="modal-form__title text-center">
              {{ T.reviewerNominationFormTitle }}
            </p>
            <div class="modal-form__field">
              <label class="modal-form__label">
                {{ T.reviewerNominationQuality }}
              </label>
              <br />
              <omegaup-radio-switch
                :value.sync="qualitySeal"
                :selected-value="qualitySeal"
              ></omegaup-radio-switch>
            </div>
            <div class="modal-form__field" data-other-tag-input>
              <vue-typeahead-bootstrap
                :data="publicTags"
                :serializer="publicTagsSerializer"
                :placeholder="T.collecionOtherTags"
                @hit="addOtherTag"
              >
              </vue-typeahead-bootstrap>
              <br />
              <div class="selected-tags-container w-100">
                <ul class="selected-tags-list">
                  <li
                    v-for="tag in publicTagsList"
                    :key="tag"
                    class="selected-tag-chip"
                  >
                    <span data-tag-name>{{ getName(tag) }}</span>
                    <button
                      type="button"
                      class="selected-tag-remove btn btn-link btn-sm p-0 ml-2 text-danger"
                      :aria-label="`${T.contestEditTagDelete}: ${getName(tag)}`"
                      :disabled="publicTagsList.length < 2"
                      @click="removeTag(tag)"
                    >
                      <font-awesome-icon :icon="['fas', 'trash']" />
                    </button>
                  </li>
                </ul>
              </div>
            </div>
            <div class="modal-form__actions">
              <button
                data-review-submit-button
                class="btn btn-primary"
                type="submit"
                :disabled="publicTagsList.length === 0"
                @click="onSubmit"
              >
                {{ T.wordsSend }}
              </button>
              <button class="btn btn-secondary" type="button" @click="onHide">
                {{ T.wordsCancel }}
              </button>
            </div>
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
import omegaup_RadioSwitch from '../RadioSwitch.vue';
import T from '../../lang';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
library.add(faTrash);

interface ProblemTag {
  text: string;
  value: string;
}

export default defineComponent({
  name: 'QualityNominationReviewerPopup',
  components: {
    'omegaup-overlay-popup': omegaup_OverlayPopup,
    'omegaup-radio-switch': omegaup_RadioSwitch,
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
    FontAwesomeIcon,
  },
  props: {
    allowUserAddTags: {
      type: Boolean,
      default: false,
    },
    levelTags: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    problemLevel: {
      type: String,
      default: '',
    },
    possibleTags: {
      type: Array as PropType<string[]>,
      default: () => [
        'problemLevelAdvancedCompetitiveProgramming',
        'problemLevelAdvancedSpecializedTopics',
        'problemLevelBasicIntroductionToProgramming',
        'problemLevelBasicKarel',
        'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
        'problemLevelIntermediateDataStructuresAndAlgorithms',
        'problemLevelIntermediateMathsInProgramming',
      ],
    },
    publicTags: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    selectedPublicTags: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    selectedPrivateTags: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    problemAlias: {
      type: String,
      default: '',
    },
    problemTitle: {
      type: String,
      default: '',
    },
  },
  emits: ['dismiss', 'rate-problem-as-reviewer'],
  data() {
    return {
      AvailableViews,
      T,
      currentView: AvailableViews.Content as AvailableViews,
      qualitySeal: true,
      publicTagsList: (this.selectedPublicTags
        ? [...this.selectedPublicTags]
        : []) as string[],
    };
  },
  computed: {
    sortedProblemTags(): ProblemTag[] {
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
    },
  },
  watch: {
    selectedPublicTags(newTags: string[]) {
      this.publicTagsList = [...(newTags ?? [])];
    },
  },
  methods: {
    addOtherTag(tag: string): void {
      if (!this.publicTagsList.includes(tag)) {
        this.publicTagsList.push(tag);
      }
    },

    publicTagsSerializer(tagname: string): string {
      if (Object.prototype.hasOwnProperty.call(T, tagname)) {
        return T[tagname];
      }
      return tagname;
    },

    getName(alias: string): string {
      return T[alias];
    },

    removeTag(name: string) {
      const pos = this.publicTagsList.indexOf(name);
      if (pos !== -1) {
        this.publicTagsList.splice(pos, 1);
      }
    },

    onHide(): void {
      this.$emit('dismiss');
    },

    onSubmit(): void {
      this.$emit('rate-problem-as-reviewer', {
        tags: this.publicTagsList,
        qualitySeal: this.qualitySeal,
      });
      this.currentView = AvailableViews.Thanks;
      setTimeout(() => this.onHide(), 2000);
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

ul.tag-select {
  height: 225px;
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

.selected-tags-container {
  max-height: 12rem;
  overflow-y: auto;
}

.selected-tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.selected-tag-chip {
  display: inline-flex;
  align-items: center;
  max-width: 100%;
  padding: 0.25rem 0.5rem;
  border: 1px solid var(--quality-nomination-tag-select-border-color);
  border-radius: 1rem;
  background: var(--quality-nomination-tag-select-background-color);
}

.selected-tag-chip [data-tag-name] {
  min-width: 0;
  overflow-wrap: anywhere;
}

.selected-tag-remove {
  flex-shrink: 0;
}
</style>
