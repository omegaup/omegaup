<template>
  <omegaup-popup
    :reviewer-nomination="true"
    :possible-tags="PROBLEM_CATEGORIES"
    @submit="$emit('submit', tag, qualitySeal, selectedPublicTags)"
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
        <br />
        <div class="card-body table-responsive">
          <table class="table table-striped">
            <thead>
              <th class="text-center" scope="col">
                {{ T.contestEditTagName }}
              </th>
              <th class="text-center" scope="col">
                {{ T.contestEditTagDelete }}
              </th>
            </thead>
            <tbody>
              <tr v-for="tag in publicTagsList" :key="tag">
                <td>{{ getName(tag) }}</td>
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

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTrash } from '@fortawesome/free-solid-svg-icons';
library.add(faTrash);

@Component({
  components: {
    'omegaup-popup': Popup,
    'omegaup-radio-switch': omegaup_RadioSwitch,
    'vue-typeahead-bootstrap': VueTypeaheadBootstrap,
    FontAwesomeIcon,
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
  publicTagsList = this.selectedPublicTags ?? [];

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
    if (!this.publicTagsList.includes(tag)) {
      this.publicTagsList.push(tag);
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
  removeTag(name: string) {
    let pos = this.publicTagsList.indexOf(name);
    this.publicTagsList.splice(pos, 1);
  }
}
</script>
