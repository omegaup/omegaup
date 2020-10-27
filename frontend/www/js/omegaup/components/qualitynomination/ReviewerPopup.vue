<template>
  <omegaup-popup
    :reviewer-nomination="true"
    :possible-tags="PROBLEM_CATEGORIES"
    @submit="$emit('submit', tag, qualitySeal)"
  >
    <template #link-title>
      {{ T.reviewerNomination }}
    </template>
    <template #popup-content="slotProps">
      <div class="title-text">
        {{ T.reviewerNominationFormTitle }}
      </div>
      <div class="form-group">
        <label class="control-label">
          {{ T.reviewerNominationQuality }}
        </label>
        <br />
        <omegaup-radio-switch
          :value.sync="qualitySeal"
          :selected-value="qualitySeal"
        ></omegaup-radio-switch>
      </div>
      <div class="form-group">
        <label class="control-label">
          {{ T.reviewerNominationCategory }}
          <ul class="tag-select">
            <li
              v-for="problemTopic in slotProps.sortedProblemTags"
              :key="problemTopic.value"
              class="tag-select"
            >
              <label class="tag-select"
                ><input
                  v-model="tag"
                  type="radio"
                  :value="problemTopic.value"
                />
                {{ problemTopic.text }}</label
              >
            </li>
          </ul></label
        >
      </div>
      <div class="form-group">
        <vue-typeahead-bootstrap
          :data="publicTags"
          :serializer="publicTagsSerializer"
          :placeholder="T.collecionOtherTags"
          @hit="addOtherTag"
        >
        </vue-typeahead-bootstrap>
      </div>
      <div class="button-row text-right">
        <button
          class="col-md-4 mr-2 btn btn-primary"
          type="submit"
          :disabled="qualitySeal && !tag"
          @click="slotProps.onSubmit"
        >
          {{ T.wordsSend }}
        </button>
        <button
          class="col-md-4 btn btn-secondary"
          type="button"
          @click="slotProps.onHide(true)"
        >
          {{ T.wordsCancel }}
        </button>
      </div>
    </template>
  </omegaup-popup>
</template>

<script lang="ts">
import { Vue, Prop, Component } from 'vue-property-decorator';
import Popup from './Popup.vue';
import omegaup_RadioSwitch from '../RadioSwitch.vue';
import T from '../../lang';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';

@Component({
  components: {
    'omegaup-popup': Popup,
    'omegaup-radio-switch': omegaup_RadioSwitch,
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
  },
})
export default class ReviewerPopup extends Vue {
  @Prop() allowUserAddTags!: boolean;
  @Prop() levelTags!: string[];
  @Prop() problemLevel!: string;
  @Prop() publicTags!: string[];
  @Prop() selectedPublicTags!: string[];
  @Prop() selectedPrivateTags!: string[];
  @Prop() problemAlias!: string;
  @Prop() problemTitle!: string;

  T = T;
  qualitySeal = true;
  tag = '';
  lista=[];

  PROBLEM_CATEGORIES = [
    'problemLevelAdvancedCompetitiveProgramming',
    'problemLevelAdvancedSpecializedTopics',
    'problemLevelBasicIntroductionToProgramming',
    'problemLevelBasicKarel',
    'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
    'problemLevelIntermediateDataStructuresAndAlgorithms',
    'problemLevelIntermediateMathsInProgramming',
  ];

  addOtherTag(tag: string): void {
    if (!this.lista.includes(tag)) {
      this.selectedTags.push(tag);
      this.lista.push(tag);
    }
  }
  publicTagsSerializer(tagname: string): string {
    if (Object.prototype.hasOwnProperty.call(T, tagname)) {
      return T[tagname];
    }
    return tagname;
  }


}
</script>
