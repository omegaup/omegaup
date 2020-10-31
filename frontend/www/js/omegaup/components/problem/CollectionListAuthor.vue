<template>
  <div>
    <h1 class="card-title">{{ T.omegaupTitleCollectionsByAuthor }}</h1>
    <div class="row">
      <div class="col col-md-4">
        <omegaup-problem-filter-authors
          :authors.sync="authors"
        ></omegaup-problem-filter-authors>
        <omegaup-problem-filter-difficulty
          v-model="selectedDifficulty"
        ></omegaup-problem-filter-difficulty>
      </div>
      <div class="col">
        <omegaup-problem-base-list
          :problems="problems"
          :logged-in="loggedIn"
          :current-tags="currentTags"
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
          @apply-filter="
            (columnName, sortOrder) =>
              $emit('apply-filter', columnName, sortOrder)
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
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-problem-filter-authors': problem_FilterAuthors,
    'omegaup-problem-base-list': problem_BaseList,
    'omegaup-problem-filter-difficulty': problem_FilterDifficulty,
  },
})
export default class CollectionList extends Vue {
  @Prop() data!: types.CollectionDetailsByAuthorPayload;
  @Prop() problems!: omegaup.Problem;
  @Prop() loggedIn!: boolean;
  @Prop() currentTags!: string[];
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

  T = T;
  authors: string[] = this.data.authors.map((element) => element.username);
  selectedDifficulty: null | string = null;
}
</script>
