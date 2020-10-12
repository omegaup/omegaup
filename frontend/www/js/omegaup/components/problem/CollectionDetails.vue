<template>
  <div>
    <h1 class="card-title">{{ title }}</h1>
    <div class="row">
      <div class="col col-md-4">
        <div>
          <omegaup-problem-filter-tags
            :tags="collectionTags"
            :public-tags="publicTags"
            :selected-tags="selectedTags"
            @emit-add-tag="addTag"
            @emit-check="check"
          ></omegaup-problem-filter-tags>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import problem_FilterTags from './FilterTags.vue';
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-problem-filter-tags': problem_FilterTags,
  },
})
export default class CollectionDetails extends Vue {
  @Prop() data!: types.CollectionDetailsPayload;

  T = T;
  type = this.data.type;
  tags: string[] = [];
  selectedTags: string[] = [];

  get collectionTags(): string[] {
    if (this.tags.length == 0) {
      this.data.collection.forEach((element) => {
        this.tags.push(element.alias);
      });
    }
    return this.tags;
  }

  get publicTags(): string[] {
    let collectionTags: string[] = this.data.collection.map((x) => x.alias);
    return this.data.publicTags.filter((x) => !collectionTags.includes(x));
  }

  get selected(): string[] {
    return this.selectedTags;
  }

  addTag(tagname: string): void {
    this.tags.push(tagname);
  }

  check(tagname: string): void {
    this.selectedTags.push(tagname);
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
