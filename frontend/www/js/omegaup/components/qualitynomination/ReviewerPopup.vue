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
      <div class="form-group d-inline-block">
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
      <omegaup-problem-tags
        :alias="problemAlias"
        :title="problemTitle"
        :initial-allow-tags="allowUserAddTags"
        :can-add-new-tags="true"
        :public-tags="publicTags"
        :level-tags="levelTags"
        :problem-level="problemLevel"
        :selected-public-tags="selectedPublicTags"
        :selected-private-tags="selectedPrivateTags"
        @emit-update-problem-level="
          (levelTag) => $emit('update-problem-level', levelTag)
        "
        @emit-add-tag="
          (alias, tagname, isPublic) =>
            $emit('add-tag', alias, tagname, isPublic)
        "
        @emit-remove-tag="
          (alias, tagname, isPublic) =>
            $emit('remove-tag', alias, tagname, isPublic)
        "
        @emit-change-allow-user-add-tag="
          (alias, title, allowTags) =>
            $emit('change-allow-user-add-tag', alias, title, allowTags)
        "
      ></omegaup-problem-tags>
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
import problem_Tags from '../problem/Tags.vue';

@Component({
  components: {
    'omegaup-popup': Popup,
    'omegaup-radio-switch': omegaup_RadioSwitch,
    'omegaup-problem-tags': problem_Tags,
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
