<template>
  <div class="container-fluid p-5 max-width mx-auto">
    <div class="row">
      <div class="col col-md-3 d-flex align-items-center">
        <a href="/problem/collection/" data-nav-problems-collection>{{
          T.problemCollectionBackCollections
        }}</a>
      </div>
      <div class="col mb-4">
        <h1 class="title-font p-0">{{ title }}</h1>
      </div>
    </div>
    <div class="row">
      <div class="col col-md-3">
        <omegaup-problem-filter-tags
          :selected-tags="selectedTags"
          :tags="availableTags"
          :public-quality-tags="publicQualityTags"
          @new-selected-tag="
            (selectedTags) =>
              $emit(
                'apply-filter',
                columnName,
                sortOrder,
                difficulty,
                quality,
                selectedTags,
              )
          "
        ></omegaup-problem-filter-tags>
        <omegaup-problem-filter-difficulty
          :selected-difficulty="difficulty"
          @change-difficulty="
            (difficulty) =>
              $emit(
                'apply-filter',
                columnName,
                sortOrder,
                difficulty,
                quality,
                selectedTags,
              )
          "
        ></omegaup-problem-filter-difficulty>
        <omegaup-problem-filter-quality
          :quality="quality"
          @change-quality="
            (quality) =>
              $emit(
                'apply-filter',
                columnName,
                sortOrder,
                difficulty,
                quality,
                selectedTags,
              )
          "
        ></omegaup-problem-filter-quality>
      </div>
      <div class="col p-0">
        <div v-if="!problems || problems.length == 0" class="card-body">
          <div class="empty-table-message">
            {{ T.courseAssignmentProblemsEmpty }}
          </div>
        </div>
        <omegaup-problem-base-list
          v-else
          :problems="problems"
          :logged-in="loggedIn"
          :selected-tags="selectedTags"
          :pager-items="pagerItems"
          :wizard-tags="wizardTags"
          :language="language"
          :languages="languages"
          :keyword="keyword"
          :modes="modes"
          :columns="columns"
          :mode="modes"
          :column="column"
          :tags="tagsList"
          :sort-order="sortOrder"
          :column-name="columnName"
          :path="`/problem/collection/${level}/`"
          @apply-filter="
            (columnName, sortOrder) =>
              $emit(
                'apply-filter',
                columnName,
                sortOrder,
                difficulty,
                quality,
                selectedTags,
              )
          "
        >
        </omegaup-problem-base-list>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import problem_FilterTags from './FilterTags.vue';
import problem_BaseList from './BaseList.vue';
import problem_FilterDifficulty from './FilterDifficulty.vue';
import problem_FilterQuality from './FilterQuality.vue';
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-problem-filter-tags': problem_FilterTags,
    'omegaup-problem-base-list': problem_BaseList,
    'omegaup-problem-filter-difficulty': problem_FilterDifficulty,
    'omegaup-problem-filter-quality': problem_FilterQuality,
  },
})
export default class CollectionList extends Vue {
  @Prop() data!: types.CollectionDetailsByLevelPayload;
  @Prop() problems!: omegaup.Problem;
  @Prop() loggedIn!: boolean;
  @Prop({ default: () => [] }) selectedTags!: string[];
  @Prop() pagerItems!: types.PageItem[];
  @Prop() wizardTags!: omegaup.Tag[];
  @Prop() language!: string;
  @Prop() languages!: string[];
  @Prop() keyword!: string;
  @Prop() modes!: string[];
  @Prop() columns!: string[];
  @Prop() mode!: string;
  @Prop() column!: string;
  @Prop({ default: () => [] }) tagsList!: string[];
  @Prop() sortOrder!: string;
  @Prop() columnName!: string;
  @Prop() difficulty!: string;
  @Prop() quality!: string;

  T = T;
  level = this.data.level;

  get publicQualityTags(): types.TagWithProblemCount[] {
    const tagNames: Set<string> = new Set(
      this.data.frequentTags.map((x) => x.name),
    );
    return this.data.publicTags.filter((tag) => !tagNames.has(tag.name));
  }

  get availableTags(): types.TagWithProblemCount[] {
    const tags: types.TagWithProblemCount[] = this.data.frequentTags;
    const selectedTagNames = new Set<string>(this.selectedTags);
    return tags.concat(
      this.publicQualityTags.filter((tag) => selectedTagNames.has(tag.name)),
    );
  }

  get title(): string {
    switch (this.level) {
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

<style scoped>
.title-font {
  font-size: 2rem;
  letter-spacing: 0.01rem;
}

.max-width {
  max-width: 75rem;
}
</style>
