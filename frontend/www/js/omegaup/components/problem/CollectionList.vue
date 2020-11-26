<template>
  <div>
    <h1 class="card-title">{{ title }}</h1>
    <div class="row">
      <div class="col col-md-4">
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
                selectedTags,
              )
          "
        ></omegaup-problem-filter-difficulty>
      </div>
      <div class="col">
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
          :languges="languages"
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
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-problem-filter-tags': problem_FilterTags,
    'omegaup-problem-base-list': problem_BaseList,
    'omegaup-problem-filter-difficulty': problem_FilterDifficulty,
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

  T = T;
  level = this.data.level;

  get availableTags(): { alias: string; total: number }[] {
    let tags: { alias: string; total: number }[] = this.data.frequentTags.slice(
      0,
      15,
    );
    let simpleTags: string[] = tags.map((x) => x.alias);
    let list: {
      alias: string;
      total: number;
    }[] = this.data.frequentTags.filter((x) => !tags.includes(x));
    this.selectedTags.forEach((element) => {
      if (!simpleTags.includes(element)) {
        tags.push(list.find((x) => x.alias === element)!);
      }
    });
    return tags;
  }

  get publicQualityTags(): { alias: string; total: number }[] {
    let tags: { alias: string; total: number }[] = this.data.frequentTags.slice(
      0,
      15,
    );
    return this.data.frequentTags.filter((x) => !tags.includes(x));
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
