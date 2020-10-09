<template>
  <div>
    <h1 class="card-title">{{ title }}</h1>
    <div class="row">
      <div class="col col-md-4">
        <div>
          <omegaup-collection-filter
            :collection="checkedTags"
            :another-tags="data.anotherTags"
            :another-tags-displayed="anotherTags"
            @emit-add-tag="addTag"
          ></omegaup-collection-filter>
          <input name="checked_tags" :value="checkedTagsList" type="hidden" />
          <input
            name="another_checked_tags"
            :value="checkedAnotherTagsList"
            type="hidden"
          />
        </div>
      </div>
    </div>
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
  anotherTags: types.CheckedTag[] = [];
  checkedTags: types.CheckedTag[] = [];

  checkTag(tagname: string, checked: boolean): void {
    this.checkedTags.push({
      tagname: tagname,
      checked: checked,
    });
  }

  get checkedTagsList(): string {
    if (this.checkedTags.length == 0) {
      this.data.collection.forEach((element) => {
        this.checkTag(element.alias, false);
      });
    }
    return JSON.stringify(this.checkedTags);
  }

  get checkedAnotherTagsList(): string {
    return JSON.stringify(this.anotherTags);
  }

  addTag(tagname: string, checked: boolean): void {
    this.anotherTags.push({
      tagname: tagname,
      checked: checked,
    });
  }

  get anotherTagsDisplayed(): string[] {
    return this.anotherTags.map((tag) => tag.tagname);
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
