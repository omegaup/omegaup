<template>
  <div class="container-fluid p-5">
    <omegaup-problem-search-bar
      :language="language"
      :languages="languages"
      :keyword="keyword"
      :tags="tags"
      :search-result-problems="searchResultProblems"
      @update-search-result-problems="
        (query) => $emit('update-search-result-problems', query)
      "
    ></omegaup-problem-search-bar>
    <button
      class="btn btn-primary mb-3"
      type="button"
      @click="showFinderWizard = true"
    >
      {{ T.wizardLinkText }}
    </button>
    <!-- TODO: Migrar el problem finder a BS4 (solo para eliminar algunos estilos) -->
    <omegaup-problem-finder
      v-show="showFinderWizard"
      :possible-tags="wizardTags"
      @close="showFinderWizard = false"
      @search-problems="wizardSearch"
    ></omegaup-problem-finder>
    <omegaup-problem-base-list
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
      :tags="tags"
      :sort-order="sortOrder"
      :column-name="columnName"
      :path="'/problem/'"
      @apply-filter="
        (columnName, sortOrder) => $emit('apply-filter', columnName, sortOrder)
      "
    ></omegaup-problem-base-list>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';

import problem_FinderWizard from './FinderWizard.vue';
import problem_SearchBar from './SearchBar.vue';
import problem_BaseList from './BaseList.vue';

@Component({
  components: {
    'omegaup-problem-base-list': problem_BaseList,
    'omegaup-problem-finder': problem_FinderWizard,
    'omegaup-problem-search-bar': problem_SearchBar,
  },
})
export default class List extends Vue {
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
  @Prop() tags!: string[];
  @Prop() sortOrder!: string;
  @Prop() columnName!: string;
  @Prop() searchResultProblems!: types.ListItem[];

  T = T;
  ui = ui;
  omegaup = omegaup;
  showFinderWizard = false;

  wizardSearch(queryParameters: omegaup.QueryParameters): void {
    this.$emit('wizard-search', queryParameters);
  }
}
</script>
