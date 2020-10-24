<template>
  <div>
    <omegaup-problem-search-bar
      :initial-language="language"
      :languages="languages"
      :initial-keyword="keyword"
      :tags="tags"
    ></omegaup-problem-search-bar>
    <a
      href="#"
      class="d-inline-block mb-3"
      role="button"
      @click="showFinderWizard = true"
    >
      {{ T.wizardLinkText }}
    </a>
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
      :tags="tags"
      :sort-order="sortOrder"
      :column-name="columnName"
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
export default class ProblemListSearch extends Vue {
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
  @Prop() tags!: string[];
  @Prop() sortOrder!: string;
  @Prop() columnName!: string;

  T = T;
  ui = ui;
  omegaup = omegaup;
  showFinderWizard = false;

  wizardSearch(queryParameters: omegaup.QueryParameters): void {
    this.$emit('wizard-search', queryParameters);
  }
}
</script>
