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
import { Vue, Component } from 'vue-property-decorator';
import Popup from './Popup.vue';
import omegaup_RadioSwitch from '../RadioSwitch.vue';
import T from '../../lang';

@Component({
  components: {
    'omegaup-popup': Popup,
    'omegaup-radio-switch': omegaup_RadioSwitch,
  },
})
export default class ReviewerPopup extends Vue {
  T = T;
  qualitySeal = true;
  tag = '';

  PROBLEM_CATEGORIES = [
    'problemLevelAdvancedCompetitiveProgramming',
    'problemLevelAdvancedSpecializedTopics',
    'problemLevelBasicIntroductionToProgramming',
    'problemLevelBasicKarel',
    'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
    'problemLevelIntermediateDataStructuresAndAlgorithms',
    'problemLevelIntermediateMathsInProgramming',
  ];
}
</script>
