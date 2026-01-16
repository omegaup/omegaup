<template>
  <div class="px-3 py-5 p-lg-5">
    <div class="row">
      <div class="col-12 col-md-4 d-flex align-items-center">
        <a href="/problem/collection/" data-nav-problems-collection>{{
          T.problemCollectionBackCollections
        }}</a>
      </div>
      <div class="col mb-4">
        <h1 class="title-font">{{ T.omegaupTitleCollectionsByAuthor }}</h1>
      </div>
    </div>
    <div class="row">
      <div class="col-12 col-lg-4 mb-3 mb-lg-0">
        <div class="row">
          <omegaup-problem-filter-authors
            :authors="authors"
            :selected-authors="selectedAuthors"
            @new-selected-author="
              (selectedAuthors) =>
                $emit(
                  'apply-filter',
                  columnName,
                  sortOrder,
                  difficulty,
                  quality,
                  selectedAuthors,
                )
            "
          ></omegaup-problem-filter-authors>
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
                  selectedAuthors,
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
                  selectedAuthors,
                )
            "
          ></omegaup-problem-filter-quality>
        </div>
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
          :languages="languages"
          :keyword="keyword"
          :modes="modes"
          :columns="columns"
          :mode="modes"
          :column="column"
          :tags="tagsList"
          :sort-order="sortOrder"
          :column-name="columnName"
          :path="'/problem/collection/author/'"
          @apply-filter="
            (columnName, sortOrder) =>
              $emit(
                'apply-filter',
                columnName,
                sortOrder,
                difficulty,
                quality,
                selectedAuthors,
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
import problem_FilterAuthors from './FilterAuthors.vue';
import problem_BaseList from './BaseList.vue';
import problem_FilterDifficulty from './FilterDifficulty.vue';
import problem_FilterQuality from './FilterQuality.vue';
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-problem-filter-authors': problem_FilterAuthors,
    'omegaup-problem-base-list': problem_BaseList,
    'omegaup-problem-filter-difficulty': problem_FilterDifficulty,
    'omegaup-problem-filter-quality': problem_FilterQuality,
  },
})
export default class CollectionList extends Vue {
  @Prop() data!: types.CollectionDetailsByAuthorPayload;
  @Prop() problems!: omegaup.Problem;
  @Prop() loggedIn!: boolean;
  @Prop() selectedTags!: string[];
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
  @Prop({ default: () => [] }) selectedAuthors!: string;

  T = T;
  authors = this.data.authorsRanking;
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
