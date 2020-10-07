<template>
  <div>
    <h1 class="card-title">{{ title }}</h1>
    <omegaup-collection-filter
      :collection="data.collection"
      :public-tags="data.anotherTags"
      :selected-public-tags="selectedPublicTags"
      @emit-add-tag="addTag"
    ></omegaup-collection-filter>
    <input name="selected_tags" :value="selectedTagsList" type="hidden" />
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import collection_Filter from './Filter.vue';
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-collection-filter': collection_Filter,
  },
})
export default class CollectionDetails extends Vue {
  @Prop() data!: types.CollectionDetailsPayload;

  T = T;
  type = this.data.type;
  selectedTags = [];

  get selectedTagsList(): string {
    return JSON.stringify(this.selectedTags);
  }

  addTag(alias: string, tagname: string, isPublic: boolean): void {
    this.selectedTags.push({
      tagname: tagname,
      public: isPublic,
    });
  }

  get selectedPublicTags(): string[] {
    return this.selectedTags
      .filter((tag) => tag.public === true)
      .map((tag) => tag.tagname);
  }

  get title(): string {
    switch (this.type) {
      case 'author':
        return T.omegaupTitleCollectionsByAuthor;
      case 'problemLevelBasicKarel':
        return T.problemLevelBasicKarel;
      case 'problemLevelBasicIntroductionToProgramming':
        return T.problemLevelBasicIntroductionToProgramming;
      case 'problemLevelIntermediateMathsInProgramming':
        return T.problemLevelIntermediateMathsInProgramming;
      case 'problemLevelIntermediateDataStructuresAndAlgorithms':
        return T.problemLevelIntermediateDataStructuresAndAlgorithms;
      case 'problemLevelIntermediateAnalysisAndDesignOfAlgorithms':
        return T.problemLevelIntermediateAnalysisAndDesignOfAlgorithms;
      case 'problemLevelAdvancedCompetitiveProgramming':
        return T.problemLevelAdvancedCompetitiveProgramming;
      case 'problemLevelAdvancedSpecializedTopics':
        return T.problemLevelAdvancedSpecializedTopics;
      default:
        return '';
    }
  }
}
</script>
