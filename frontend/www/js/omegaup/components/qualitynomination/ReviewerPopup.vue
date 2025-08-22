<template>
  <omegaup-overlay-popup @dismiss="onHide">
    <transition name="fade">
      <form data-reviewewr-popup class="h-auto w-auto" @submit.prevent="">
        <div class="container-fluid d-flex align-items-start flex-column">
          <template v-if="currentView === AvailableViews.Content">
            <p class="h4 font-weight-bold pb-4 text-center w-100">
              {{ T.reviewerNominationFormTitle }}
            </p>
            <div class="form-group w-100">
              <label class="control-label">
                {{ T.reviewerNominationQuality }}
              </label>
              <omegaup-radio-switch
                :value.sync="qualitySeal"
                :selected-value="qualitySeal"
              ></omegaup-radio-switch>
            </div>
            <div class="form-group w-100">
              <label class="control-label w-100">
                {{ T.reviewerNominationCategory }}
              </label>
              <ul
                class="tag-select border"
                :class="{
                  'border-primary':
                    reviewedProblemLevel &&
                    reviewedProblemLevel !== problemLevel,
                }"
              >
                <li
                  v-for="problemTopic in sortedProblemTags"
                  :key="problemTopic.value"
                  class="tag-select"
                >
                  <label class="tag-label"
                    ><input
                      v-model="selectedProblemLevel"
                      type="radio"
                      :value="problemTopic.value"
                    />
                    {{ problemTopic.text }}</label
                  >
                </li>
              </ul>
            </div>
            <div class="form-group w-100" data-other-tag-input>
              <label>{{ T.wordsPublicTags }}</label>
              <vue-typeahead-bootstrap
                :data="publicTags"
                :serializer="publicTagsSerializer"
                :placeholder="T.publicTagsPlaceholder"
                @hit="addOtherTag"
              >
              </vue-typeahead-bootstrap>
              <div class="card-body table-responsive w-100">
                <table class="table table-striped w-100">
                  <thead>
                    <th class="text-center" scope="col">
                      {{ T.contestEditTagName }}
                    </th>
                    <th class="text-center" scope="col">
                      {{ T.contestEditTagDelete }}
                    </th>
                  </thead>
                  <tbody>
                    <tr
                      v-for="tag in uniqueTags"
                      :key="tag"
                      class="border"
                      :class="{
                        'border-primary': exclusiveReviewedTags.includes(tag),
                      }"
                    >
                      <td data-tag-name>{{ getName(tag) }}</td>
                      <td class="text-center">
                        <button
                          type="button"
                          class="btn btn-danger"
                          :disabled="publicTagsList.length < 2"
                          @click="removeTag(tag)"
                        >
                          <font-awesome-icon :icon="['fas', 'trash']" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="text-right">
              <button
                data-review-submit-button
                class="btn btn-primary mr-3"
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
import { Vue, Prop, Component } from 'vue-property-decorator';
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

@Component({
  components: {
    'omegaup-overlay-popup': omegaup_OverlayPopup,
    'omegaup-radio-switch': omegaup_RadioSwitch,
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
    FontAwesomeIcon,
  },
})
export default class ReviewerPopup extends Vue {
  @Prop() allowUserAddTags!: boolean;
  @Prop() levelTags!: string[];
  @Prop() problemLevel!: string;
  @Prop({
    default: () => [
      'problemLevelAdvancedCompetitiveProgramming',
      'problemLevelAdvancedSpecializedTopics',
      'problemLevelBasicIntroductionToProgramming',
      'problemLevelBasicKarel',
      'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
      'problemLevelIntermediateDataStructuresAndAlgorithms',
      'problemLevelIntermediateMathsInProgramming',
    ],
  })
  possibleTags!: string[];
  @Prop({ default: () => [] }) publicTags!: string[];
  @Prop() reviewedProblemLevel!: null | string;
  @Prop() reviewedQualitySeal!: boolean;
  @Prop({ default: () => [] }) reviewedPublicTags!: string[];
  @Prop({ default: () => [] }) selectedPublicTags!: string[];
  @Prop({ default: () => [] }) selectedPrivateTags!: string[];
  @Prop() problemAlias!: string;
  @Prop() problemTitle!: string;

  AvailableViews = AvailableViews;
  T = T;
  currentView: AvailableViews = AvailableViews.Content;
  qualitySeal = this.reviewedQualitySeal;
  publicTagsList = Array.from(this.selectedPublicTags);
  currentReviewedTags = this.reviewedPublicTags;
  selectedProblemLevel = this.reviewedProblemLevel
    ? this.reviewedProblemLevel
    : this.problemLevel;

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

  get uniqueTags(): string[] {
    return [...new Set([...this.publicTagsList, ...this.reviewedPublicTags])];
  }

  get exclusiveReviewedTags(): string[] {
    return this.reviewedPublicTags.filter(
      (tag) => !this.selectedPublicTags.includes(tag),
    );
  }

  addOtherTag(tag: string): void {
    if (!this.publicTagsList.includes(tag)) {
      this.publicTagsList.push(tag);
    }
    if (!this.reviewedPublicTags.includes(tag)) {
      this.reviewedPublicTags = [...this.reviewedPublicTags, tag];
    }
  }

  publicTagsSerializer(tagname: string): string {
    if (Object.prototype.hasOwnProperty.call(T, tagname)) {
      return T[tagname];
    }
    return tagname;
  }

  getName(alias: string): string {
    return T[alias];
  }

  removeTag(name: string): void {
    const publicTagIndex = this.publicTagsList.indexOf(name);
    if (publicTagIndex !== -1) {
      this.publicTagsList.splice(publicTagIndex, 1);
    }

    const reviewedTagIndex = this.reviewedPublicTags.indexOf(name);
    if (reviewedTagIndex !== -1) {
      this.reviewedPublicTags = this.reviewedPublicTags.filter(
        (tag) => tag !== name,
      );
    }
  }

  onHide(): void {
    this.$emit('dismiss');
  }

  onSubmit(): void {
    this.$emit('rate-problem-as-reviewer', {
      tags: this.uniqueTags,
      level: this.selectedProblemLevel,
      quality_seal: this.qualitySeal,
    });
    this.currentView = AvailableViews.Thanks;
    setTimeout(() => this.onHide(), 2000);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
ul.tag-select {
  overflow: auto;
  border: 1px solid var(--quality-nomination-tag-select-border-color);
  border-radius: 0.25rem;
  background: var(--quality-nomination-tag-select-background-color);
  list-style-type: none;
}

.tag-label {
  width: -webkit-fill-available;
  margin-bottom: 0;
  padding-bottom: 0.5rem;
}
</style>
