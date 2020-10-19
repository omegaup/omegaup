<template>
  <div>
    <h1 class="card-title">{{ title }}</h1>
    <div class="row">
      <div class="col col-md-4">
        <omegaup-problem-filter-tags
          :tags.sync="tags"
          :public-tags="publicTags"
        ></omegaup-problem-filter-tags>
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
  @Prop() data!: types.CollectionDetailsByLevelPayload;

  T = T;
  type = this.data.type;
  tags: string[] = this.data.collection.map((element) => element.alias);

  get publicTags(): string[] {
    let tags: string[] = this.data.collection.map((x) => x.alias);
    return this.data.publicTags.filter((x) => !tags.includes(x));
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
